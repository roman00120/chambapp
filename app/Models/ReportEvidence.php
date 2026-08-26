<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportEvidence extends Model
{
    use HasFactory;

    protected $table = 'report_evidence';

    protected $fillable = [
        'user_report_id',
        'uploaded_by_user_id',
        'storage_path',
        'mime_type',
        'original_name',
        'file_size',
    ];

    public function userReport(): BelongsTo
    {
        return $this->belongsTo(UserReport::class, 'user_report_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
