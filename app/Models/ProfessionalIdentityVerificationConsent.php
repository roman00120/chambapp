<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalIdentityVerificationConsent extends Model
{
    protected $fillable = [
        'professional_id',
        'identity_verification_id',
        'consent_version',
        'privacy_notice_version',
        'purpose',
        'accepted_at',
        'ip_hash',
        'user_agent_hash',
    ];

    protected $hidden = ['ip_hash', 'user_agent_hash'];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function identityVerification(): BelongsTo
    {
        return $this->belongsTo(ProfessionalIdentityVerification::class);
    }
}
