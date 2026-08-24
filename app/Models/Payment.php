<?php

namespace App\Models;

use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'job_request_id', 'client_id', 'professional_id', 'provider', 'kind',
        'external_payment_id', 'external_reference', 'currency', 'economic_model_version',
        'base_amount', 'client_service_fee_percent', 'client_service_fee',
        'professional_commission_percent', 'professional_commission', 'customer_total',
        'platform_gross_fee', 'professional_amount_before_external_costs', 'gross_amount',
        'platform_fee_percent', 'platform_fee', 'provider_fee', 'professional_amount',
        'external_preference_id', 'checkout_url', 'checkout_expires_at', 'status', 'tip_amount', 'tip_platform_fee', 'tip_professional_amount',
        'paid_at', 'refunded_at', 'refunded_amount', 'last_reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'client_service_fee_percent' => 'decimal:2',
            'client_service_fee' => 'decimal:2',
            'professional_commission_percent' => 'decimal:2',
            'professional_commission' => 'decimal:2',
            'customer_total' => 'decimal:2',
            'platform_gross_fee' => 'decimal:2',
            'professional_amount_before_external_costs' => 'decimal:2',
            'platform_fee_percent' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'provider_fee' => 'decimal:2',
            'professional_amount' => 'decimal:2',
            'tip_amount' => 'decimal:2',
            'tip_platform_fee' => 'decimal:2',
            'tip_professional_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'kind' => PaymentKind::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'checkout_expires_at' => 'datetime',
            'last_reconciled_at' => 'datetime',
        ];
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
