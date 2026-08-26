<?php

namespace App\Http\Controllers\Professional;

use App\Enums\PriceType;
use App\Exceptions\IdentityVerificationRequiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professional\StoreServiceRequest;
use App\Http\Requests\Professional\UpdateServiceRequest;
use App\Models\Category;
use App\Models\Service;
use App\Services\ProfessionalIdentityVerificationService;
use App\Services\ProfessionalProfileService;
use App\Services\ProfessionalServiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalServiceController extends Controller
{
    public function __construct(
        private readonly ProfessionalProfileService $profiles,
        private readonly ProfessionalServiceManager $services,
    ) {}

    public function index(Request $request): View
    {
        $profile = $this->profiles->profileFor($request->user());
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
        $profile = $this->profiles->profileFor($request->user());
        try {
            $service = $this->services->create($profile, $request->validated(), $request->file('images', []));
        } catch (IdentityVerificationRequiredException $exception) {
            return redirect()->route('professional.identity-verification.show')->withErrors([
                'identity_verification' => $exception->getMessage(),
            ]);
        }

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
        $this->services->update($service, $request->validated(), $request->file('images', []));

        return redirect()->route('professional.services.edit', $service)->with('status', 'Servicio actualizado correctamente.');
    }

    public function toggle(Service $service, ProfessionalIdentityVerificationService $identityVerification): RedirectResponse
    {
        $this->authorize('update', $service);
        if (! $service->is_active) {
            try {
                $identityVerification->ensureProfessionalCanAcceptJobs($service->professional);
            } catch (IdentityVerificationRequiredException $exception) {
                return redirect()->route('professional.identity-verification.show')->withErrors([
                    'identity_verification' => $exception->getMessage(),
                ]);
            }
        }
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('status', $service->is_active ? 'Servicio activado correctamente.' : 'Servicio desactivado correctamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);
        $service->delete();

        return redirect()->route('professional.services.index')->with('status', 'Servicio eliminado.');
    }
}
