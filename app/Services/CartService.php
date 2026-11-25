<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const MAX_QUANTITY = 99;

    private const MAX_LINES = 40;

    public function summary(string $key): array
    {
        $cart = Cart::query()->where('session_key', $key)->first();
        $items = $cart?->items()->with('product')->orderBy('id')->get() ?? new Collection;

        return [
            'items' => $items,
            'total' => $items->sum(fn (CartItem $item) => $item->quantity * $item->product->price_minor),
            'ready' => $items->isNotEmpty() && $items->every(
                fn (CartItem $item) => $item->product->is_active && $item->product->stock >= $item->quantity,
            ),
        ];
    }

    public function add(string $key, int $productId, int $quantity): void
    {
        DB::transaction(function () use ($key, $productId, $quantity): void {
            Cart::query()->firstOrCreate(['session_key' => $key]);
            $cart = $this->lockCart($key)->items();
            $item = (clone $cart)->firstOrNew(['product_id' => $productId]);
            if (! $item->exists && $cart->count() >= self::MAX_LINES) {
                throw ValidationException::withMessages(['cart' => 'Korpa može da sadrži najviše 40 različitih proizvoda.']);
            }

            $product = Product::query()->findOrFail($productId);
            $item->quantity = ($item->quantity ?? 0) + $quantity;
            $this->saveQuantity($item, $product);
        }, 3);
    }

    public function update(string $key, int $itemId, int $quantity): void
    {
        DB::transaction(function () use ($key, $itemId, $quantity): void {
            $item = $this->lockCart($key)->items()->with('product')->findOrFail($itemId);
            $item->quantity = $quantity;
            $this->saveQuantity($item, $item->product);
        }, 3);
    }

    public function remove(string $key, int $itemId): void
    {
        DB::transaction(function () use ($key, $itemId): void {
            $this->lockCart($key)->items()->findOrFail($itemId)->delete();
        }, 3);
    }

    private function lockCart(string $key): Cart
    {
        return Cart::query()->where('session_key', $key)->lockForUpdate()->firstOrFail();
    }

    private function saveQuantity(CartItem $item, Product $product): void
    {
        if (! $product->is_active || $item->quantity > min(self::MAX_QUANTITY, $product->stock)) {
            throw ValidationException::withMessages(['quantity' => 'Tražena količina trenutno nije dostupna.']);
        }

        $item->save();
    }
}
