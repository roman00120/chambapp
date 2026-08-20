<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Favorite;
use App\Models\ProfessionalProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()->favorites()
            ->whereHas('professional', fn ($profile) => $profile
                ->where('verification_status', VerificationStatus::VERIFIED->value)
                ->whereHas('user', fn ($user) => $user
                    ->where('status', UserStatus::ACTIVE->value)
                    ->where('role', UserRole::PROFESSIONAL->value)))
            ->with(['professional.user'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('client.favorites', compact('favorites'));
    }

    public function toggle(Request $request, ProfessionalProfile $professionalProfile): RedirectResponse
    {
        $professionalProfile->load('user');
        abort_unless(
            $professionalProfile->verification_status === VerificationStatus::VERIFIED
                && $professionalProfile->user?->status === UserStatus::ACTIVE
                && $professionalProfile->user?->role === UserRole::PROFESSIONAL,
            404,
        );

        $favorite = Favorite::query()->where([
            'user_id' => $request->user()->getKey(),
            'professional_id' => $professionalProfile->getKey(),
        ])->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Profesional eliminado de tus favoritos.';
        } else {
            Favorite::create([
                'user_id' => $request->user()->getKey(),
                'professional_id' => $professionalProfile->getKey(),
            ]);
            $message = 'Profesional guardado en tus favoritos.';
        }

        return back()->with('status', $message);
    }
}
