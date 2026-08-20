<?php

namespace App\Models;

use App\Enums\JobDisputeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_request_id', 'opened_by', 'reason', 'description', 'status',
        'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobDisputeStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
