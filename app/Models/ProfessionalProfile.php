<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use Database\Factories\ProfessionalProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProfessionalProfile extends Model
{
    /** @use HasFactory<ProfessionalProfileFactory> */
    use HasFactory;

    protected $hidden = [
        'mercadopago_access_token', 'mercadopago_refresh_token',
    ];

    protected $fillable = [
        'user_id', 'bio', 'experience_years', 'city', 'state', 'postal_code',
        'latitude', 'longitude', 'verification_status', 'profile_photo',
        'average_rating', 'total_reviews', 'total_completed_jobs',
        'verified_by', 'verified_at', 'verification_rejection_reason',
        'is_available', 'availability_status', 'last_latitude', 'last_longitude',
        'location_updated_at', 'service_radius_km',
        'mercadopago_user_id', 'mercadopago_access_token', 'mercadopago_refresh_token',
        'mercadopago_public_key', 'mercadopago_token_expires_at', 'mercadopago_connected_at',
        'profile_theme', 'profile_banner', 'profile_frame', 'profile_animation', 'profile_accent',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'verification_status' => VerificationStatus::class,
            'is_available' => 'boolean',
            'availability_status' => AvailabilityStatus::class,
            'last_latitude' => 'decimal:7',
            'last_longitude' => 'decimal:7',
            'location_updated_at' => 'datetime',
            'service_radius_km' => 'integer',
            'average_rating' => 'decimal:2',
            'total_reviews' => 'integer',
            'total_completed_jobs' => 'integer',
            'verified_at' => 'datetime',
            'mercadopago_access_token' => 'encrypted',
            'mercadopago_refresh_token' => 'encrypted',
            'mercadopago_token_expires_at' => 'datetime',
            'mercadopago_connected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function identityVerification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProfessionalIdentityVerification::class, 'professional_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ProfessionalCredential::class, 'professional_id');
    }

    public function hasVerifiedIdentity(): bool
    {
        return app(\App\Services\ProfessionalIdentityVerificationService::class)->hasVerifiedIdentity($this);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'professional_id');
    }

    public function jobQuotes(): HasMany
    {
        return $this->hasMany(JobQuote::class, 'professional_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(JobInvitation::class, 'professional_id');
    }

    public function isLocationFresh(): bool
    {
        return $this->location_updated_at?->gte(now()->subMinutes((int) config('chambapp.on_demand.location_freshness_minutes', 30))) ?? false;
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo) {
            return $this->user?->avatar_url;
        }

        if (preg_match('/^https?:\/\//i', $this->profile_photo)) {
            return $this->profile_photo;
        }

        if (str_starts_with($this->profile_photo, '/')) {
            return $this->profile_photo;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_photo);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profilePhotoUrl();
    }

    public function canReceiveImmediateJobs(): bool
    {
        return $this->is_available
            && $this->availability_status === AvailabilityStatus::AVAILABLE
            && $this->isLocationFresh();
    }

    public function completionPercentage(): int
    {
        $fields = [
            filled($this->bio),
            $this->experience_years > 0,
            filled($this->city),
            filled($this->state),
            filled($this->user?->phone),
        ];

        return (int) round((count(array_filter($fields)) / count($fields)) * 100);
    }

    public function isComplete(): bool
    {
        return $this->completionPercentage() === 100;
    }

    public function achievements(): array
    {
        $items = [];
        if ($this->total_completed_jobs >= 1) {
            $items[] = ['icon' => 'bi-check2-circle', 'title' => 'Primer trabajo', 'text' => 'Completaste tu primera chamba.'];
        }
        if ($this->total_completed_jobs >= 5) {
            $items[] = ['icon' => 'bi-award', 'title' => 'Constante', 'text' => 'Cinco trabajos completados correctamente.'];
        }
        if ($this->total_completed_jobs >= 10) {
            $items[] = ['icon' => 'bi-trophy', 'title' => 'Experto en acción', 'text' => 'Diez trabajos completados.'];
        }
        if ($this->total_reviews >= 3 && (float) $this->average_rating >= 4.5) {
            $items[] = ['icon' => 'bi-star-fill', 'title' => 'Muy recomendado', 'text' => 'Calificación promedio superior a 4.5.'];
        }

        return $items;
    }

    public function isMercadoPagoConnected(): bool
    {
        return filled($this->mercadopago_user_id)
            && filled($this->mercadopago_access_token)
            && ($this->mercadopago_token_expires_at === null
                || $this->mercadopago_token_expires_at->isFuture()
                || filled($this->mercadopago_refresh_token));
    }

    public function isPubliclyVisible(): bool
    {
        return $this->verification_status === VerificationStatus::VERIFIED
            && $this->user?->status === UserStatus::ACTIVE
            && $this->user?->role === UserRole::PROFESSIONAL;
    }

    public function verificationLabel(): string
    {
        return match ($this->verification_status) {
            VerificationStatus::PENDING => 'Revisión de perfil pendiente',
            VerificationStatus::VERIFIED => 'Perfil habilitado',
            VerificationStatus::REJECTED => 'Perfil rechazado',
            default => 'Perfil sin revisar',
        };
    }

    public function verificationBadgeVariant(): string
    {
        return match ($this->verification_status) {
            VerificationStatus::PENDING => 'pending',
            VerificationStatus::VERIFIED => 'verified',
            VerificationStatus::REJECTED => 'danger',
            default => 'neutral',
        };
    }

    public function jobRequests(): HasMany
    {
        return $this->hasMany(JobRequest::class, 'professional_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'professional_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'professional_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'professional_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'professional_id');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function commerceOrders(): HasMany
    {
        return $this->hasMany(CommerceOrder::class, 'professional_id');
    }
}
