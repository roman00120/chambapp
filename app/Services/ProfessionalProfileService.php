<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProfessionalProfileService
{
    public function profileFor(User $user): ProfessionalProfile
    {
        return $user->professionalProfile()->firstOrCreate(
            ['user_id' => $user->getKey()],
            ['verification_status' => VerificationStatus::UNVERIFIED],
        );
    }

    public function update(User $user, ProfessionalProfile $profile, array $data, ?UploadedFile $photo = null): ProfessionalProfile
    {
        $newPhoto = null;
        $oldPhoto = $profile->profile_photo;

        try {
            if ($photo) {
                $newPhoto = $photo->store('profiles', 'public');
            }

            DB::transaction(function () use ($user, $profile, $data, $newPhoto): void {
                $user->update(['name' => $data['name'], 'phone' => $data['phone']]);
                $profile->update([
                    'bio' => $data['bio'] ?? null,
                    'experience_years' => $data['experience_years'],
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    ...($newPhoto ? ['profile_photo' => $newPhoto] : []),
                ]);
            });
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }
            throw $exception;
        }

        if ($newPhoto && $oldPhoto && Str::startsWith($oldPhoto, 'profiles/')) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return $profile->fresh('user');
    }
}
