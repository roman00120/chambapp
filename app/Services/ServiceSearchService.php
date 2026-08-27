<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class ServiceSearchService
{
    public function __construct(private readonly ProfessionalIdentityVerificationService $identityVerification) {}

    public function search(array $filters): LengthAwarePaginator
    {
        $query = Service::query()
            ->active()
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true))
            ->whereHas('professional', function (Builder $profile): void {
                $profile->publiclyVisible();
                $this->identityVerification->applyOperationalEligibility($profile);
            })
            ->with([
                'category:id,name,slug,icon',
                'professional:id,user_id,bio,city,state,verification_status,profile_photo,average_rating,total_reviews,total_completed_jobs',
                'professional.identityVerification:id,professional_id,status,expires_at',
                'professional.user:id,name,email,status,role',
                'coverImage:id,service_id,path,alt_text,sort_order,is_cover',
            ]);

        $this->applyTextSearch($query, $filters['q'] ?? null);
        $this->applyFilters($query, $filters);
        $this->applyOrdering($query, $filters['sort'] ?? 'relevant', $filters['q'] ?? null);

        return $query->paginate(12)->withQueryString();
    }

    private function applyTextSearch(Builder $query, ?string $term): void
    {
        if (! $term) {
            return;
        }

        $like = '%'.$term.'%';
        $query->where(function (Builder $search) use ($like): void {
            $search->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                ->orWhereHas('professional', function (Builder $profile) use ($like): void {
                    $profile->where('bio', 'like', $like)
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $like));
                });
        });
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $category) => $category->where('slug', $filters['category']));
        }

        if (! empty($filters['city'])) {
            $query->whereHas('professional', fn (Builder $profile) => $profile->where('city', 'like', '%'.$filters['city'].'%'));
        }

        if (! empty($filters['state'])) {
            $query->whereHas('professional', fn (Builder $profile) => $profile->where('state', 'like', '%'.$filters['state'].'%'));
        }

        if (! empty($filters['price_type'])) {
            $query->where('price_type', $filters['price_type']);
        }

        if ($filters['min_price'] ?? null) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if ($filters['max_price'] ?? null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if ($filters['rating'] ?? null) {
            $query->whereHas('professional', fn (Builder $profile) => $profile->where('average_rating', '>=', $filters['rating']));
        }

        if (filter_var($filters['verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('professional.identityVerification', fn (Builder $verification) => $verification
                ->where('status', \App\Enums\IdentityVerificationStatus::VERIFIED->value)
                ->where(fn (Builder $expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now())));
        }
    }

    private function applyOrdering(Builder $query, string $sort, ?string $term): void
    {
        if ($sort === 'relevant' && $term) {
            $like = '%'.$term.'%';
            $query->orderByRaw('CASE WHEN title LIKE ? THEN 0 WHEN description LIKE ? THEN 1 ELSE 2 END', [$like, $like]);
        }

        match ($sort) {
            'rating' => $query->orderByDesc($this->professionalRatingSubquery())
                ->orderByDesc($this->professionalReviewsSubquery()),
            'price_low' => $query->orderByRaw('price IS NULL')->orderBy('price'),
            'price_high' => $query->orderByRaw('price IS NULL')->orderByDesc('price'),
            'recent' => $query->latest('created_at'),
            default => $query->orderByDesc('is_featured')
                ->orderByRaw('featured_until IS NULL')
                ->orderByDesc('featured_until')
                ->orderByDesc($this->professionalRatingSubquery())
                ->orderByDesc($this->professionalReviewsSubquery())
                ->latest('created_at'),
        };
    }

    private function professionalRatingSubquery(): Expression
    {
        return new Expression('(SELECT average_rating FROM professional_profiles WHERE professional_profiles.id = services.professional_id LIMIT 1)');
    }

    private function professionalReviewsSubquery(): Expression
    {
        return new Expression('(SELECT total_reviews FROM professional_profiles WHERE professional_profiles.id = services.professional_id LIMIT 1)');
    }
}
