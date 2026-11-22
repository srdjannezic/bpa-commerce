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

    /**
     * Return the guest cart items, total in minor units and checkout readiness.
     *
     * @return array{items: Collection<int, CartItem>, total: int, ready: bool}
     */
    public function summary(string $key): array
    {
        $cart = Cart::query()->where('session_key', $key)->first();
        $items = $cart?->items()->with('product')->orderBy('id')->get() ?? new Collection;

        return [
            'items' => $items,
            'total' => $items->sum(/** Calculate the line subtotal in minor units. */ fn (CartItem $item) => $item->quantity * $item->product->price_minor),
            'ready' => $items->isNotEmpty() && $items->every(
                /** Check that the product can fulfill the requested quantity. */
                fn (CartItem $item) => $item->product->is_active && $item->product->stock >= $item->quantity,
            ),
        ];
    }

    /**
     * Add to a product quantity under a cart lock and enforce cart and stock limits.
     *
     * @throws ValidationException
     */
    public function add(string $key, int $productId, int $quantity): void
    {
        DB::transaction(/** Lock the cart and add to the stored product quantity. */ function () use ($key, $productId, $quantity): void {
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

    /**
     * Replace an item quantity after checking product availability.
     *
     * @throws ValidationException
     */
    public function update(string $key, int $itemId, int $quantity): void
    {
        DB::transaction(/** Lock the cart and replace the selected line quantity. */ function () use ($key, $itemId, $quantity): void {
            $item = $this->lockCart($key)->items()->with('product')->findOrFail($itemId);
            $item->quantity = $quantity;
            $this->saveQuantity($item, $item->product);
        }, 3);
    }

    /**
     * Delete an item from the locked cart for this guest session.
     */
    public function remove(string $key, int $itemId): void
    {
        DB::transaction(/** Lock the cart and remove the selected line. */ function () use ($key, $itemId): void {
            $this->lockCart($key)->items()->findOrFail($itemId)->delete();
        }, 3);
    }

    /**
     * Find the guest cart and lock its row for the current transaction.
     */
    private function lockCart(string $key): Cart
    {
        return Cart::query()->where('session_key', $key)->lockForUpdate()->firstOrFail();
    }

    /**
     * Save an item after checking the product status and available quantity.
     *
     * @throws ValidationException
     */
    private function saveQuantity(CartItem $item, Product $product): void
    {
        if (! $product->is_active || $item->quantity > min(self::MAX_QUANTITY, $product->stock)) {
            throw ValidationException::withMessages(['quantity' => 'Tražena količina trenutno nije dostupna.']);
        }

        $item->save();
    }
}
