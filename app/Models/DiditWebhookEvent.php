<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiditWebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'webhook_type',
        'provider_session_id',
        'payload_hash',
        'processing_status',
        'failure_code',
        'received_at',
        'processed_at',
    ];

    protected $hidden = ['payload_hash'];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
