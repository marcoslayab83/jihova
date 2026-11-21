<?php

namespace App\Models;

use App\Enums\StoreStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'category_id',
        'business_name',
        'slug',
        'tagline',
        'description',
        'logo_url',
        'banner_url',
        'accent_color',
        'phone',
        'status',
        'commission_rate',
        'rating',
        'is_special_taxpayer',
        'rif',
        'bcv_rate',
        'legal_name',
        'iva_condition',
        'fiscal_address',
        'seniat_invoice_correlative',
        'seniat_control_correlative',
    ];

    protected function casts(): array
    {
        return [
            'status' => StoreStatus::class,
            'commission_rate' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_special_taxpayer' => 'boolean',
            'bcv_rate' => 'decimal:4',
            'seniat_invoice_correlative' => 'integer',
            'seniat_control_correlative' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(StoreBranch::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function adBanners(): HasMany
    {
        return $this->hasMany(MallAdBanner::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(StoreSale::class);
    }

    public function storeUsers(): HasMany
    {
        return $this->hasMany(StoreUser::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->withPivot(['role', 'store_branch_id', 'is_active'])
            ->withTimestamps();
    }

    public function fiscalInvoices(): HasMany
    {
        return $this->hasMany(FiscalInvoice::class);
    }

    public function fiscalPurchases(): HasMany
    {
        return $this->hasMany(FiscalPurchase::class);
    }

    public function withholdingVouchers(): HasMany
    {
        return $this->hasMany(WithholdingVoucher::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', StoreStatus::Active->value);
    }
}
