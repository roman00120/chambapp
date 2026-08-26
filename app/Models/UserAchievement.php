<?php

namespace App\Models;

use App\Enums\AchievementLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'level',
        'earned_at',
        'revoked_at',
        'revocation_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'level' => AchievementLevel::class,
            'earned_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }
}
