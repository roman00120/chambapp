<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Services\PaymentCalculationService;
use Database\Factories\JobQuoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobQuote extends Model
{
    /** @use HasFactory<JobQuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'job_request_id', 'professional_id', 'amount', 'description', 'status',
        'expires_at', 'accepted_at', 'rejected_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => QuoteStatus::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function isExpired(): bool
    {
        return $this->status === QuoteStatus::PENDING
            && $this->expires_at?->isPast() === true;
    }

    public function markExpiredIfNeeded(): bool
    {
        if (! $this->isExpired()) {
            return false;
        }

        $this->forceFill(['status' => QuoteStatus::EXPIRED])->save();

        return true;
    }

    public function formattedAmount(): string
    {
        return app(PaymentCalculationService::class)->formatAmount((string) $this->amount);
    }
}
