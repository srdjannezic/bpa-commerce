<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price_minor', 'stock', 'image_url', 'is_active', 'category_id'];

    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'stock' => 'integer', 'revision' => 'integer', 'is_active' => 'boolean'];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if ($term === null) {
            return;
        }

        $escapedTerm = str_replace('!', '!!', $term);
        $escapedTerm = str_replace(['%', '_'], ['!%', '!_'], $escapedTerm);
        $containsPattern = '%'.$escapedTerm.'%';

        $query->where(function (Builder $match) use ($containsPattern): void {
            $match->whereRaw("name LIKE ? ESCAPE '!'", [$containsPattern])
                ->orWhereRaw("description LIKE ? ESCAPE '!'", [$containsPattern]);
        });
    }

    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        if (isset($filters['min_price'])) {
            $query->where('price_minor', '>=', Money::fromDecimal($filters['min_price']));
        }

        if (isset($filters['max_price'])) {
            $query->where('price_minor', '<=', Money::fromDecimal($filters['max_price']));
        }

        if (isset($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (isset($filters['stock'])) {
            $query->where('stock', $filters['stock'] === 'available' ? '>' : '=', 0);
        }
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
