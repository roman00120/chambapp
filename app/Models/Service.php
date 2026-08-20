<?php

namespace App\Models;

use App\Enums\PriceType;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'professional_id', 'category_id', 'title', 'slug', 'description',
        'price', 'price_type', 'is_active', 'is_featured', 'featured_until',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_type' => PriceType::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'featured_until' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOwnedBy(Builder $query, ProfessionalProfile|int $professional): Builder
    {
        $professionalId = $professional instanceof ProfessionalProfile ? $professional->getKey() : $professional;

        return $query->where('professional_id', $professionalId);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class);
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(ServiceImage::class)->where('is_cover', true)->orderBy('sort_order');
    }

    public function formattedPrice(): string
    {
        if ($this->price_type === PriceType::QUOTE || $this->price === null) {
            return 'Cotización';
        }

        $amount = '$'.number_format((float) $this->price, 0, '.', ',').' MXN';

        return $this->price_type === PriceType::STARTING_AT ? 'Desde '.$amount : $amount;
    }

    public function jobRequests(): HasMany
    {
        return $this->hasMany(JobRequest::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
