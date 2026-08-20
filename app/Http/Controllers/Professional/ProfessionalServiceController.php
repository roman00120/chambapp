<?php

namespace App\Http\Controllers\Professional;

use App\Enums\PriceType;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professional\StoreServiceRequest;
use App\Http\Requests\Professional\UpdateServiceRequest;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Services\ServiceImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfessionalServiceController extends Controller
{
    public function __construct(private readonly ServiceImageManager $images) {}

    public function index(Request $request): View
    {
        $profile = $this->profileFor($request);
        $services = $profile->services()
            ->with(['category', 'coverImage'])
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('professional.services.index', compact('services', 'profile'));
    }

    public function create(Request $request): View
    {
        $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('professional.services.create', [
            'categories' => $categories,
            'priceTypes' => PriceType::cases(),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $data = $request->validated();
        $files = $request->file('images', []);

        $service = DB::transaction(function () use ($data, $files, $profile): Service {
            $service = $profile->services()->create([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($profile, $data['title']),
                'description' => $data['description'],
                'price_type' => $data['price_type'],
                'price' => $data['price_type'] === PriceType::QUOTE->value ? null : ($data['price'] ?? null),
                'is_active' => true,
                'is_featured' => false,
            ]);

            $this->images->store($service, $files, isset($data['cover_index']) ? (int) $data['cover_index'] : null);

            return $service;
        });

        return redirect()->route('professional.services.edit', $service)->with('status', 'Servicio publicado correctamente.');
    }

    public function edit(Service $service): View
    {
        $this->authorize('view', $service);
        $service->load(['category', 'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);
        $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('professional.services.edit', [
            'service' => $service,
            'categories' => $categories,
            'priceTypes' => PriceType::cases(),
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);
        $data = $request->validated();
        $files = $request->file('images', []);
        $cover = ! empty($data['cover_image_id'])
            ? $service->images()->whereKey($data['cover_image_id'])->firstOrFail()
            : null;

        DB::transaction(function () use ($data, $files, $service, $cover): void {
            $service->update([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'price_type' => $data['price_type'],
                'price' => $data['price_type'] === PriceType::QUOTE->value ? null : ($data['price'] ?? null),
            ]);

            $this->images->store($service, $files, isset($data['cover_index']) ? (int) $data['cover_index'] : null);

            if ($cover) {
                $this->images->setCover($service, $cover);
            }
        });

        return redirect()->route('professional.services.edit', $service)->with('status', 'Servicio actualizado correctamente.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $this->authorize('update', $service);
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('status', $service->is_active ? 'Servicio activado correctamente.' : 'Servicio desactivado correctamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);
        $service->delete();

        return redirect()->route('professional.services.index')->with('status', 'Servicio eliminado.');
    }

    private function profileFor(Request $request): ProfessionalProfile
    {
        return $request->user()->professionalProfile()->firstOrCreate([
            'user_id' => $request->user()->getKey(),
        ], [
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);
    }

    private function uniqueSlug(ProfessionalProfile $profile, string $title): string
    {
        $base = Str::limit(Str::slug($title), 70, '');

        do {
            $slug = trim($base, '-').'-'.Str::lower(Str::random(8));
        } while ($profile->services()->where('slug', $slug)->exists());

        return $slug;
    }
}
