<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_id', 'kind', 'service_id', 'item_key', 'amount', 'currency',
        'provider', 'financial_status', 'status', 'external_reference', 'external_preference_id',
        'external_payment_id', 'checkout_url', 'checkout_expires_at', 'metadata', 'paid_at',
        'refunded_amount', 'provider_updated_at', 'refunded_at', 'last_reconciled_at', 'fulfillment_error',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'financial_status' => PaymentStatus::class,
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'checkout_expires_at' => 'datetime',
            'refunded_amount' => 'decimal:2',
            'provider_updated_at' => 'datetime',
            'refunded_at' => 'datetime',
            'last_reconciled_at' => 'datetime',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommerceOrderEvent::class);
    }
}
