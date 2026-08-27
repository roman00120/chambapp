<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, MustVerifyEmailTrait, Notifiable;

    protected $fillable = [
        'name', 'email', 'google_id', 'avatar_url', 'phone', 'password', 'role', 'status', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function isClient(): bool
    {
        return $this->canActAsClient();
    }

    public function isProfessional(): bool
    {
        return $this->canActAsProfessional();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isCreator(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        $creatorEmails = config('chambapp.creator_emails', ['gerawx@gmail.com', 'romy00120@gmail.com']);
        if (! is_array($creatorEmails)) {
            $creatorEmails = array_filter(array_map('trim', explode(',', (string) $creatorEmails)));
        }
        $creatorEmails = array_map('strtolower', (array) $creatorEmails);

        return in_array(strtolower((string) $this->email), $creatorEmails, true);
    }

    public function canActAsClient(): bool
    {
        return $this->isActive();
    }

    public function canActAsProfessional(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->role === UserRole::PROFESSIONAL) {
            return true;
        }

        if ($this->role === UserRole::ADMIN) {
            return true;
        }

        return $this->relationLoaded('professionalProfile')
            ? $this->professionalProfile !== null
            : $this->professionalProfile()->exists();
    }

    /**
     * @return list<string>
     */
    public function capabilities(): array
    {
        $caps = [];
        if ($this->canActAsClient()) {
            $caps[] = 'client';
        }
        if ($this->canActAsProfessional()) {
            $caps[] = 'professional';
        }
        if ($this->isAdmin()) {
            $caps[] = 'admin';
        }

        return $caps;
    }

    public function resolveActiveMode(?string $requestedMode = null): string
    {
        if ($requestedMode === 'professional' && $this->canActAsProfessional()) {
            return 'professional';
        }

        if ($requestedMode === 'client' && $this->canActAsClient()) {
            return 'client';
        }

        if ($this->role === UserRole::PROFESSIONAL) {
            return 'professional';
        }

        return 'client';
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function hasGoogleLogin(): bool
    {
        return filled($this->google_id);
    }

    public function profilePhotoUrl(): ?string
    {
        $profile = $this->relationLoaded('professionalProfile')
            ? $this->professionalProfile
            : $this->professionalProfile()->first();

        if ($profile && $profile->profile_photo) {
            return $profile->profilePhotoUrl();
        }

        return $this->avatar_url;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profilePhotoUrl();
    }

    public function dashboardRoute(): string
    {
        $mode = session('active_mode');
        if ($mode === 'professional' && $this->canActAsProfessional()) {
            return 'professional.dashboard';
        }
        if ($mode === 'client' && $this->canActAsClient()) {
            return 'client.dashboard';
        }
        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }
        if ($this->role === UserRole::PROFESSIONAL) {
            return 'professional.dashboard';
        }

        return 'client.dashboard';
    }

    public function professionalProfile(): HasOne
    {
        return $this->hasOne(ProfessionalProfile::class);
    }

    public function reviewedIdentityVerifications(): HasMany
    {
        return $this->hasMany(ProfessionalIdentityVerification::class, 'reviewed_by');
    }

    public function legalAcceptances(): HasMany
    {
        return $this->hasMany(LegalAcceptance::class);
    }

    public function jobRequests(): HasMany
    {
        return $this->hasMany(JobRequest::class, 'client_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'client_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'client_id');
    }

    public function isAccountActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::SUSPENDED;
    }

    public function isBanned(): bool
    {
        return $this->status === UserStatus::BLOCKED;
    }

    public function canPerformMarketplaceActions(): bool
    {
        return $this->isAccountActive() && ! $this->isSuspended() && ! $this->isBanned();
    }

    public function activeYellowCardsCount(): int
    {
        return $this->disciplinaryActions()
            ->where('action_type', \App\Enums\DisciplinaryActionType::YELLOW_CARD->value)
            ->where('status', \App\Enums\DisciplinaryActionStatus::ACTIVE->value)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    public function submittedUserReports(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reporter_id');
    }

    public function receivedUserReports(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_id');
    }

    public function disciplinaryActions(): HasMany
    {
        return $this->hasMany(DisciplinaryAction::class, 'user_id');
    }

    public function disciplinaryAppeals(): HasMany
    {
        return $this->hasMany(DisciplinaryAppeal::class, 'user_id');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'client_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function openedDisputes(): HasMany
    {
        return $this->hasMany(JobDispute::class, 'opened_by');
    }

    public function resolvedDisputes(): HasMany
    {
        return $this->hasMany(JobDispute::class, 'resolved_by');
    }

    public function reviewedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    public function adminAuditLogs(): HasMany
    {
        return $this->hasMany(AdminAuditLog::class, 'admin_id');
    }
}
