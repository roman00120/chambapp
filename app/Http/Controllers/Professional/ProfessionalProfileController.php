<?php

namespace App\Http\Controllers\Professional;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professional\UpdateProfessionalProfileRequest;
use App\Models\ProfessionalProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfessionalProfileController extends Controller
{
    public function show(Request $request): View
    {
        $profile = $this->profileFor($request)->load('user');
        $this->authorize('view', $profile);

        return view('professional.profile.show', compact('profile'));
    }

    public function edit(Request $request): View
    {
        $profile = $this->profileFor($request)->load('user');
        $this->authorize('update', $profile);

        return view('professional.profile.edit', compact('profile'));
    }

    public function update(UpdateProfessionalProfileRequest $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $this->authorize('update', $profile);
        $data = $request->validated();
        $newPhoto = null;
        $oldPhoto = $profile->profile_photo;

        try {
            if ($request->hasFile('profile_photo')) {
                $newPhoto = $request->file('profile_photo')->store('profiles', 'public');
            }

            DB::transaction(function () use ($request, $profile, $data, $newPhoto): void {
                $request->user()->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                ]);

                $profile->update([
                    'bio' => $data['bio'] ?? null,
                    'experience_years' => $data['experience_years'],
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    ...($newPhoto ? ['profile_photo' => $newPhoto] : []),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto && $this->isManagedPhoto($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()->route('professional.profile.show')->with('status', 'Perfil actualizado correctamente.');
    }

    private function profileFor(Request $request): ProfessionalProfile
    {
        return $request->user()->professionalProfile()->firstOrCreate([
            'user_id' => $request->user()->getKey(),
        ], [
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);
    }

    private function isManagedPhoto(?string $path): bool
    {
        return $path !== null && Str::startsWith($path, 'profiles/');
    }
}
