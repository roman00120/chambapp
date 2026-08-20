<?php

namespace App\Services;

use App\Models\ProfessionalProfile;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ProfessionalRatingService
{
    public function recalculate(ProfessionalProfile $professional): ProfessionalProfile
    {
        return DB::transaction(function () use ($professional): ProfessionalProfile {
            $locked = ProfessionalProfile::query()->lockForUpdate()->findOrFail($professional->getKey());
            $summary = Review::query()
                ->visible()
                ->where('professional_id', $locked->getKey())
                ->selectRaw('COUNT(*) as total_reviews, COALESCE(SUM(rating), 0) as rating_sum')
                ->first();
            $totalReviews = (int) $summary->total_reviews;
            $average = $totalReviews === 0
                ? '0.00'
                : number_format(((int) $summary->rating_sum) / $totalReviews, 2, '.', '');
            $locked->forceFill([
                'average_rating' => $average,
                'total_reviews' => $totalReviews,
            ])->save();

            return $locked->fresh();
        });
    }
}
