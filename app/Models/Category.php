<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public const ICON_MAP = [
        'bolt' => 'lightning-charge',
        'electricidad' => 'lightning-charge',
        'electricity' => 'lightning-charge',
        'lightning' => 'lightning-charge',
        'sparkles' => 'stars',
        'limpieza' => 'stars',
        'cleaning' => 'stars',
        'clean' => 'stars',
    ];

    public function bootstrapIcon(): string
    {
        $raw = strtolower(trim((string) $this->icon));
        if (isset(self::ICON_MAP[$raw])) {
            return self::ICON_MAP[$raw];
        }

        if ($raw !== '') {
            return $raw;
        }

        $slug = strtolower(trim((string) $this->slug));
        if (isset(self::ICON_MAP[$slug])) {
            return self::ICON_MAP[$slug];
        }

        return 'grid';
    }

    public function getBootstrapIconAttribute(): string
    {
        return $this->bootstrapIcon();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
