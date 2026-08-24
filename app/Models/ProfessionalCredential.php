<?php

namespace App\Models;

use App\Enums\ProfessionalCredentialStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalCredential extends Model
{
    protected $fillable = [
        'professional_id', 'category_id', 'credential_type', 'provider',
        'provider_reference', 'status', 'submitted_at', 'verified_at',
        'rejected_at', 'expires_at', 'reason_code', 'reviewed_by',
        'reviewed_at', 'review_reason',
    ];

    protected $hidden = ['provider_reference'];

    protected function casts(): array
    {
        return [
            'status' => ProfessionalCredentialStatus::class,
            'submitted_at' => 'datetime', 'verified_at' => 'datetime',
            'rejected_at' => 'datetime', 'expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
