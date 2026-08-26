<?php

namespace App\Services;

use App\Enums\IdentityVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRegistrationService
{
    public function __construct(private readonly LegalAcceptanceService $legal) {}

    public function register(array $data): User
    {
        $role = UserRole::from($data['account_type'] ?? $data['role']);
        $legalDocuments = $this->legal->validateRegistration($data, $role);

        return DB::transaction(function () use ($data, $role, $legalDocuments): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => $role,
                'status' => UserStatus::ACTIVE,
            ]);

            if ($role === UserRole::PROFESSIONAL) {
                $profile = ProfessionalProfile::create([
                    'user_id' => $user->id,
                    'verification_status' => VerificationStatus::UNVERIFIED,
                ]);
                $profile->identityVerification()->create(['status' => IdentityVerificationStatus::NOT_STARTED]);
            }

            $this->legal->record(
                $user,
                $legalDocuments,
                (string) ($data['legal_platform'] ?? 'unknown'),
                $data['legal_ip'] ?? null,
                $data['legal_user_agent'] ?? null,
            );

            return $user->load('professionalProfile');
        });
    }
}
