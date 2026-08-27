<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchServicesRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProfessionalResource;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Services\ServiceSearchService;
use App\Services\ProfessionalIdentityVerificationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicController extends Controller
{
    public function categories(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::query()->active()->orderBy('sort_order')->orderBy('name')->get());
    }

    public function services(SearchServicesRequest $request, ServiceSearchService $search): AnonymousResourceCollection
    {
        $filters = $request->validated();
        if ($request->filled('search') && empty($filters['q'])) {
            $filters['q'] = trim((string) $request->input('search'));
        }

        return ServiceResource::collection($search->search($filters));
    }

    public function service(Service $service, ProfessionalIdentityVerificationService $identityVerification): ServiceResource
    {
        $service->load(['category', 'professional.user', 'images', 'coverImage']);
        abort_unless($service->is_active && $service->category?->is_active && $service->professional?->isPubliclyVisible(), 404);
        abort_unless($identityVerification->professionalCanAcceptJobs($service->professional), 404);

        return new ServiceResource($service);
    }

    public function professional(ProfessionalProfile $professional): ProfessionalResource
    {
        $professional->load([
            'user',
            'identityVerification',
            'services' => fn ($query) => $query->active()->with(['category', 'coverImage'])->latest(),
            'reviews' => fn ($query) => $query->visible()->with('client')->latest()->limit(10),
        ]);
        abort_unless($professional->isPubliclyVisible(), 404);

        return new ProfessionalResource($professional);
    }

    public function reviews(ProfessionalProfile $professional): AnonymousResourceCollection
    {
        $professional->load('user');
        abort_unless($professional->isPubliclyVisible(), 404);

        return ReviewResource::collection(
            $professional->reviews()->visible()->with('client')->latest()->paginate(15),
        );
    }
}
