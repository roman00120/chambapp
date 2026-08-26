<?php

namespace App\Models;

use App\Enums\DisciplinaryAppealStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'disciplinary_action_id',
        'user_id',
        'appeal_text',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => DisciplinaryAppealStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function disciplinaryAction(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryAction::class, 'disciplinary_action_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }
}
