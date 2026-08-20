<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_request_id', 'professional_id', 'distance_km', 'status',
        'invited_at', 'viewed_at', 'responded_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'status' => InvitationStatus::class,
            'invited_at' => 'datetime',
            'viewed_at' => 'datetime',
            'responded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [InvitationStatus::PENDING->value, InvitationStatus::VIEWED->value]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }
}
