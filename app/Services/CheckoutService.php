<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private UserService $users) {}

    public function place(string $key, array $delivery, int $expectedTotal): Order
    {
        return DB::transaction(function () use ($key, $delivery, $expectedTotal): Order {
            $cart = Cart::query()->where('session_key', $key)->lockForUpdate()->first();
            $items = $cart?->items()->orderBy('product_id')->get();

            if ($items === null || $items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Korpa je prazna.']);
            }

            $products = Product::query()
                ->whereKey($items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $lines = [];
            $stockRows = [];

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product || ! $product->is_active || $product->stock < $item->quantity) {
                    $name = $product?->name ?? 'Proizvod';

                    throw ValidationException::withMessages(['cart' => "Proveri dostupnu količinu za proizvod „{$name}“."]);
                }

                $total += $product->price_minor * $item->quantity;
                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price_minor' => $product->price_minor,
                    'quantity' => $item->quantity,
                ];
                $product->stock -= $item->quantity;
                $product->revision++;
                $product->updated_at = now();
                $stockRows[] = $product->getAttributes();
            }

            if ($total !== $expectedTotal) {
                throw ValidationException::withMessages(['cart' => 'Iznos u korpi se promenio. Proveri novo stanje i ponovo potvrdi porudžbinu.']);
            }

            $customer = $this->users->findOrCreateCustomer($delivery['recipient_email'], $delivery['recipient_name']);
            $order = new Order($delivery + [
                'total_minor' => $total,
                'payment_method' => 'cod',
                'payment_status' => PaymentStatus::Pending,
            ]);

            $order->user()->associate($customer);
            $cart->orders()->save($order);

            OrderItem::query()->insert(array_map(
                fn (array $line) => ['order_id' => $order->id] + $line,
                $lines,
            ));
            Product::query()->upsert($stockRows, ['id'], ['stock', 'revision', 'updated_at']);
            $cart->items()->delete();

            return $order;
        }, 3);
    }
}
