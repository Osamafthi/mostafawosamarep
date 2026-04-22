<?php

namespace App\Models;

use App\Services\SlugService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock',
        'category_id',
        'image_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock' => 'integer',
            'category_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug) || $product->isDirty('name')) {
                $product->slug = app(SlugService::class)->uniqueFor(
                    'products',
                    $product->name ?? '',
                    $product->exists ? $product->id : null
                );
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function effectivePrice(): float
    {
        $discount = $this->discount_price;

        return $discount !== null && (float) $discount > 0
            ? (float) $discount
            : (float) $this->price;
    }
}
