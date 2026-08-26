<?php

namespace App\Models;

use App\Enums\DisciplinaryActionStatus;
use App\Enums\DisciplinaryActionType;
use App\Enums\ReportSeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DisciplinaryAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_report_id',
        'action_type',
        'severity',
        'reason_code',
        'reason_text',
        'status',
        'issued_by_admin_id',
        'issued_at',
        'expires_at',
        'revoked_at',
        'revoked_by_admin_id',
        'revocation_reason',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'action_type' => DisciplinaryActionType::class,
            'severity' => ReportSeverity::class,
            'status' => DisciplinaryActionStatus::class,
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sourceReport(): BelongsTo
    {
        return $this->belongsTo(UserReport::class, 'source_report_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_admin_id');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_admin_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(DisciplinaryAppeal::class, 'disciplinary_action_id');
    }

    public function latestAppeal(): HasOne
    {
        return $this->hasOne(DisciplinaryAppeal::class, 'disciplinary_action_id')->latestOfMany();
    }

    public function isActive(): bool
    {
        if ($this->status !== DisciplinaryActionStatus::ACTIVE) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
