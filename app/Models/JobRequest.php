<?php

namespace App\Models;

use App\Enums\JobStatus;
use App\Enums\ServiceMode;
use App\Services\PaymentCalculationService;
use Database\Factories\JobRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class JobRequest extends Model
{
    /** @use HasFactory<JobRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id', 'professional_id', 'service_id', 'category_id', 'service_mode', 'title', 'description',
        'address', 'city', 'state', 'postal_code', 'latitude', 'longitude',
        'requested_date', 'agreed_price', 'status', 'accepted_at', 'started_at',
        'completed_at', 'cancelled_at',
        'finished_at', 'completion_code', 'completion_code_expires_at', 'completion_confirmed_at', 'cancellation_reason', 'scheduled_for', 'scheduled_slot',
        'search_started_at', 'search_expires_at', 'matched_at', 'on_the_way_at',
        'arrived_at', 'search_round', 'search_radius_km', 'photo_paths',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'requested_date' => 'datetime',
            'agreed_price' => 'decimal:2',
            'status' => JobStatus::class,
            'service_mode' => ServiceMode::class,
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'finished_at' => 'datetime',
            'completion_code' => 'encrypted',
            'completion_code_expires_at' => 'datetime',
            'completion_confirmed_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'search_started_at' => 'datetime',
            'search_expires_at' => 'datetime',
            'matched_at' => 'datetime',
            'on_the_way_at' => 'datetime',
            'arrived_at' => 'datetime',
            'search_round' => 'integer',
            'search_radius_km' => 'decimal:2',
            'photo_paths' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(JobInvitation::class);
    }

    public function isImmediate(): bool
    {
        return $this->service_mode === ServiceMode::IMMEDIATE;
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(JobQuote::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function approvedPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', 'approved');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(JobDispute::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function formattedAgreedPrice(): string
    {
        return $this->agreed_price === null
            ? 'Por acordar'
            : app(PaymentCalculationService::class)->formatAmount((string) $this->agreed_price);
    }

    public function formattedRequestedDate(): string
    {
        return $this->requested_date?->locale('es')->translatedFormat('d M Y, g:i a') ?? 'Fecha por definir';
    }
}
