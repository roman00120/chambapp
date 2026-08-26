<?php

namespace App\Models;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_id',
        'job_request_id',
        'category',
        'severity_reported',
        'description',
        'status',
        'resolution',
        'admin_notes_private',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => ReportCategory::class,
            'severity_reported' => ReportSeverity::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class, 'job_request_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class, 'user_report_id');
    }

    public function disciplinaryAction(): HasOne
    {
        return $this->hasOne(DisciplinaryAction::class, 'source_report_id');
    }
}
