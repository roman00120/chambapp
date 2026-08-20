<?php

namespace App\Services;

use App\Models\ProfessionalProfile;
use DomainException;

class ProfessionalAvailabilityService
{
    public function update(ProfessionalProfile $profile, array $data): ProfessionalProfile
    {
        $updates = [
            'is_available' => (bool) $data['is_available'],
            'service_radius_km' => (int) $data['service_radius_km'],
        ];

        if (isset($data['latitude'], $data['longitude'])) {
            $updates += [
                'last_latitude' => $data['latitude'],
                'last_longitude' => $data['longitude'],
                'location_updated_at' => now(),
            ];
        }

        if ($updates['is_available'] && ($profile->last_latitude === null || $profile->last_longitude === null)
            && ! isset($updates['last_latitude'], $updates['last_longitude'])) {
            throw new DomainException('Comparte tu ubicación antes de activar disponibilidad.');
        }

        $profile->forceFill($updates)->save();

        return $profile->fresh();
    }
}
