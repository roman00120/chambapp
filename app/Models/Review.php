<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use InvalidArgumentException;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'job_request_id', 'client_id', 'professional_id', 'rating', 'comment',
        'is_hidden', 'hidden_by', 'hidden_at', 'moderation_reason',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_hidden' => 'boolean',
            'hidden_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $review): void {
            if ($review->rating < 1 || $review->rating > 5) {
                throw new InvalidArgumentException('La calificación debe estar entre 1 y 5.');
            }
        });
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

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function publicClientName(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->client?->name), -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) < 2) {
            return $parts[0] ?? 'Cliente Chambapp';
        }

        return $parts[0].' '.mb_substr($parts[count($parts) - 1], 0, 1).'.';
    }
}
