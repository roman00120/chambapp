<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProfessionalResource;
use App\Models\Favorite;
use App\Models\ProfessionalProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = $request->user()->favorites()
            ->whereHas('professional', fn ($profile) => $profile
                ->where('verification_status', VerificationStatus::VERIFIED->value)
                ->whereHas('user', fn ($user) => $user
                    ->where('status', UserStatus::ACTIVE->value)
                    ->where('role', UserRole::PROFESSIONAL->value)))
            ->with('professional.user')
            ->latest()
            ->paginate(15);

        return ProfessionalResource::collection($favorites->through(fn ($favorite) => $favorite->professional));
    }

    public function store(Request $request, ProfessionalProfile $professional): JsonResponse
    {
        $professional->load('user');
        abort_unless($professional->isPubliclyVisible(), 404);
        $favorite = Favorite::query()->firstOrCreate([
            'user_id' => $request->user()->getKey(),
            'professional_id' => $professional->getKey(),
        ]);

        return response()->json([
            'data' => new ProfessionalResource($professional),
            'message' => 'Profesional guardado en favoritos.',
        ], $favorite->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, ProfessionalProfile $professional): JsonResponse
    {
        Favorite::query()->where([
            'user_id' => $request->user()->getKey(),
            'professional_id' => $professional->getKey(),
        ])->delete();

        return response()->json(['data' => null, 'message' => 'Profesional eliminado de favoritos.']);
    }
}
