<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityVerificationTransfer extends Model
{
    protected $fillable = [
        'professional_id', 'identity_verification_id', 'token_hash',
        'provider_session_id', 'hosted_url', 'expires_at', 'consumed_at', 'revoked_at',
    ];

    protected $hidden = ['token_hash', 'provider_session_id', 'hosted_url'];

    protected function casts(): array
    {
        return [
            'hosted_url' => 'encrypted',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function identityVerification(): BelongsTo
    {
        return $this->belongsTo(ProfessionalIdentityVerification::class, 'identity_verification_id');
    }
}
