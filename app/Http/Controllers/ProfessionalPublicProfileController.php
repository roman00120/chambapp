<?php

namespace App\Http\Controllers;

use App\Models\ProfessionalProfile;
use Illuminate\View\View;

class ProfessionalPublicProfileController extends Controller
{
    public function __invoke(ProfessionalProfile $professionalProfile): View
    {
        $professionalProfile->load('user');
        abort_unless($professionalProfile->isPubliclyVisible(), 404);

        $professionalProfile->load([
            'services' => fn ($query) => $query->active()
                ->whereHas('category', fn ($category) => $category->where('is_active', true))
                ->with(['category', 'coverImage', 'professional.user'])
                ->latest(),
            'reviews' => fn ($query) => $query
                ->visible()
                ->with(['client:id,name', 'jobRequest:id,service_id', 'jobRequest.service:id,title'])
                ->latest()
                ->limit(5),
        ]);

        return view('professional.profile.public', [
            'profile' => $professionalProfile,
            'isFavorite' => auth()->check() && auth()->user()->favorites()->where('professional_id', $professionalProfile->getKey())->exists(),
        ]);
    }
}
