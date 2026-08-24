<?php

namespace App\Models;

use App\Enums\IdentityVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalIdentityVerification extends Model
{
    protected $fillable = [
        'professional_id', 'verification_provider', 'provider_verification_id',
        'status', 'verification_level', 'document_type', 'submitted_at',
        'verified_at', 'rejected_at', 'expires_at', 'reason_code',
        'reviewed_by', 'reviewed_at', 'review_reason',
    ];

    protected $hidden = ['provider_verification_id'];

    protected function casts(): array
    {
        return [
            'status' => IdentityVerificationStatus::class,
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isVerified(): bool
    {
        return $this->status === IdentityVerificationStatus::VERIFIED
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
