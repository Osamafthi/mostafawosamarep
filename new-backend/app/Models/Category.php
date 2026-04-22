<?php

namespace App\Models;

use App\Services\SlugService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (empty($category->slug) || $category->isDirty('name')) {
                $category->slug = app(SlugService::class)->uniqueFor(
                    'categories',
                    $category->name ?? '',
                    $category->exists ? $category->id : null
                );
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
