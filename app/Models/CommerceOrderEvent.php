<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceOrderEvent extends Model
{
    protected $fillable = ['commerce_order_id', 'event_type', 'provider_event_id', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }
}
