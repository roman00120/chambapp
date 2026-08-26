<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalIdentityVerificationEvent extends Model
{
    protected $fillable = [
        'identity_verification_id',
        'provider_session_id',
        'source',
        'from_status',
        'to_status',
        'reason_code',
        'occurred_at',
    ];

    protected $hidden = ['provider_session_id'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function identityVerification(): BelongsTo
    {
        return $this->belongsTo(ProfessionalIdentityVerification::class);
    }
}
