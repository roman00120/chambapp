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
        return $this->role === UserRole::CLIENT;
    }

    public function isProfessional(): bool
    {
        return $this->role === UserRole::PROFESSIONAL;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
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
        return match ($this->role) {
            UserRole::CLIENT => 'client.dashboard',
            UserRole::PROFESSIONAL => 'professional.dashboard',
            UserRole::ADMIN => 'admin.dashboard',
        };
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
