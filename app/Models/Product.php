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

    /**
     * Cast prices, stock, revisions and the active flag to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'stock' => 'integer', 'revision' => 'integer', 'is_active' => 'boolean'];
    }

    /**
     * Limit the query to products visible in the catalog.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Match a literal substring of the product name or description. A null term leaves the query unrestricted.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if ($term === null) {
            return;
        }

        $escapedTerm = str_replace('!', '!!', $term);
        $escapedTerm = str_replace(['%', '_'], ['!%', '!_'], $escapedTerm);
        $containsPattern = '%'.$escapedTerm.'%';

        $query->where(/** Group the name and description alternatives. */ function (Builder $match) use ($containsPattern): void {
            $match->whereRaw("name LIKE ? ESCAPE '!'", [$containsPattern])
                ->orWhereRaw("description LIKE ? ESCAPE '!'", [$containsPattern]);
        });
    }

    /**
     * Apply inclusive price bounds, a category and a stock filter when provided.
     *
     * @param  Builder<Product>  $query
     * @param  array{min_price?: string|null, max_price?: string|null, category?: int|string|null, stock?: 'available'|'sold_out'|null, ...}  $filters
     */
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

    /**
     * Get the category assigned to this product.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get cart items that reference this product.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get order items that reference this product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
