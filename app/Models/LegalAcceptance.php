<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalAcceptance extends Model
{
    protected $fillable = [
        'user_id', 'document_type', 'document_version', 'accepted_at',
        'platform', 'ip_hash', 'user_agent_hash',
    ];

    protected $hidden = ['ip_hash', 'user_agent_hash'];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
