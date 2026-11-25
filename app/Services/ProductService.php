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

    public function available(int $id): Product
    {
        return Product::query()->active()->with('category:id,name')->findOrFail($id);
    }

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

    public function inventory(): LengthAwarePaginator
    {
        return Product::query()->with('category:id,name')->latest('id')->paginate(self::PER_PAGE);
    }

    public function find(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function save(array $data, ?int $id = null): Product
    {
        $data['price_minor'] = Money::fromDecimal($data['price']);
        unset($data['price']);

        if ($id === null) {
            return Product::query()->create($data);
        }

        return DB::transaction(function () use ($data, $id): Product {
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

    public function archive(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $product = Product::query()->lockForUpdate()->findOrFail($id);
            $product->is_active = false;
            $product->revision++;
            $product->save();
        }, 3);
    }
}
