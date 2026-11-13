<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MallAdBanner extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'product_id',
        'headline',
        'subline',
        'badge',
        'tag',
        'image_url',
        'target_url',
        'placement',
        'is_active',
        'clicks_count',
        'impressions_count',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'clicks_count' => 'integer',
            'impressions_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTitleAttribute(): string
    {
        return (string) ($this->attributes['headline'] ?? '');
    }

    public function getSubtitleAttribute(): ?string
    {
        return $this->attributes['subline'] ?? null;
    }

    public function getCtaTextAttribute(): string
    {
        return (string) ($this->attributes['tag'] ?? 'Ver Oferta');
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    #[Scope]
    protected function forHero(Builder $query): Builder
    {
        return $query->whereIn('placement', ['hero_spotlight', 'both']);
    }

    #[Scope]
    protected function forVitrina(Builder $query): Builder
    {
        return $query->whereIn('placement', ['vitrina_home', 'both']);
    }
}
