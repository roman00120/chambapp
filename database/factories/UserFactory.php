<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => '55'.fake()->unique()->numerify('########'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    public function client(): static
    {
        return $this->state(['role' => UserRole::CLIENT]);
    }

    public function professional(): static
    {
        return $this->state(['role' => UserRole::PROFESSIONAL]);
    }

    public function professionalVerified(array $profileAttributes = []): static
    {
        return $this->state(['role' => UserRole::PROFESSIONAL])
            ->afterCreating(function (User $user) use ($profileAttributes) {
                if (! $user->professionalProfile) {
                    ProfessionalProfile::factory()
                        ->verifiedIdentity()
                        ->create(array_merge(['user_id' => $user->id], $profileAttributes));
                } else {
                    $user->professionalProfile->update($profileAttributes);
                    ProfessionalProfile::factory()->verifiedIdentity()->afterCreating(fn () => null);
                }
            });
    }

    public function professionalPending(array $profileAttributes = []): static
    {
        return $this->state(['role' => UserRole::PROFESSIONAL])
            ->afterCreating(function (User $user) use ($profileAttributes) {
                if (! $user->professionalProfile) {
                    ProfessionalProfile::factory()
                        ->pendingIdentity()
                        ->create(array_merge(['user_id' => $user->id], $profileAttributes));
                }
            });
    }

    public function professionalRejected(?string $reason = 'Documento no coincide', array $profileAttributes = []): static
    {
        return $this->state(['role' => UserRole::PROFESSIONAL])
            ->afterCreating(function (User $user) use ($reason, $profileAttributes) {
                if (! $user->professionalProfile) {
                    ProfessionalProfile::factory()
                        ->rejectedIdentity($reason)
                        ->create(array_merge(['user_id' => $user->id], $profileAttributes));
                }
            });
    }

    public function professionalExpired(array $profileAttributes = []): static
    {
        return $this->state(['role' => UserRole::PROFESSIONAL])
            ->afterCreating(function (User $user) use ($profileAttributes) {
                if (! $user->professionalProfile) {
                    ProfessionalProfile::factory()
                        ->expiredIdentity()
                        ->create(array_merge(['user_id' => $user->id], $profileAttributes));
                }
            });
    }

    public function professionalNotStarted(array $profileAttributes = []): static
    {
        return $this->state(['role' => UserRole::PROFESSIONAL])
            ->afterCreating(function (User $user) use ($profileAttributes) {
                if (! $user->professionalProfile) {
                    ProfessionalProfile::factory()
                        ->notStartedIdentity()
                        ->create(array_merge(['user_id' => $user->id], $profileAttributes));
                }
            });
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::ADMIN]);
    }

    public function suspended(): static
    {
        return $this->state(['status' => UserStatus::SUSPENDED]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
