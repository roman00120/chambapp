<?php

namespace App\Models;

use Database\Factories\ServiceImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceImage extends Model
{
    /** @use HasFactory<ServiceImageFactory> */
    use HasFactory;

    protected $fillable = ['service_id', 'path', 'alt_text', 'sort_order', 'is_cover'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_cover' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
