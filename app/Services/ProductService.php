<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    private const PER_PAGE = 12;

    /**
     * Paginate active products and carry the applied filters into pagination links.
     *
     * @param  array{q?: string|null, min_price?: string|null, max_price?: string|null, category?: int|string|null, stock?: 'available'|'sold_out'|null}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function catalog(int $page, array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->active()
            ->search($filters['q'] ?? null)
            ->filter($filters)
            ->latest('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->appends($filters);
    }

    /**
     * Find an active product with its category.
     */
    public function available(int $id): Product
    {
        return Product::query()->active()->with('category:id,name')->findOrFail($id);
    }

    /**
     * Return up to four other active products from the same category.
     *
     * @return Collection<int, Product>
     */
    public function related(Product $product): Collection
    {
        if ($product->category === null) {
            return new Collection;
        }

        return $product->category->products()->active()
            ->whereKeyNot($product->id)
            ->orderByDesc('stock')
            ->latest('id')
            ->limit(4)
            ->get();
    }

    /**
     * Paginate the inventory, including archived products.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function inventory(): LengthAwarePaginator
    {
        return Product::query()->with('category:id,name')->latest('id')->paginate(self::PER_PAGE);
    }

    /**
     * Find a product by ID for inventory management.
     */
    public function find(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    /**
     * Create a product or save an update after checking its revision.
     *
     * @param  array{name: string, description: string, price: string, stock: int|string, image_url?: string|null, is_active: bool|int|string, category_id?: int|string|null, revision?: int|string}  $data
     *
     * @throws ValidationException If the submitted revision is stale.
     */
    public function save(array $data, ?int $id = null): Product
    {
        $data['price_minor'] = Money::fromDecimal($data['price']);
        unset($data['price']);

        if ($id === null) {
            return Product::query()->create($data);
        }

        return DB::transaction(/** Check the revision under a row lock before updating. */ function () use ($data, $id): Product {
            $product = Product::query()->lockForUpdate()->findOrFail($id);
            if ($product->revision !== (int) $data['revision']) {
                throw ValidationException::withMessages([
                    'product' => 'Proizvod je u međuvremenu izmenjen. Osveži stranicu pre ponovnog čuvanja.',
                ]);
            }

            unset($data['revision']);
            $product->fill($data);
            $product->revision++;
            $product->save();

            return $product;
        }, 3);
    }

    /**
     * Deactivate a product and increment its revision in one transaction.
     */
    public function archive(int $id): void
    {
        DB::transaction(/** Lock the product before changing its visibility and revision. */ function () use ($id): void {
            $product = Product::query()->lockForUpdate()->findOrFail($id);
            $product->is_active = false;
            $product->revision++;
            $product->save();
        }, 3);
    }
}
