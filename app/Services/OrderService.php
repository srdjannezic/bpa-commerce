<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Jobs\SendPaymentConfirmation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Return the newest orders for the guest cart with their item counts.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function history(string $key): LengthAwarePaginator
    {
        return $this->guestOrders($key)->withCount('items')->latest('id')->paginate(15);
    }

    /**
     * Find an order and its line items within the guest cart.
     */
    public function forGuest(string $key, int $id): Order
    {
        return $this->guestOrders($key)->with('items')->findOrFail($id);
    }

    /**
     * List the newest orders for administrators, optionally filtered by payment status.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function all(?PaymentStatus $status): LengthAwarePaginator
    {
        return Order::query()
            ->with('user:id,name,email')
            ->withCount('items')
            ->when($status, /** Apply the supplied payment status to the order query. */ fn ($query) => $query->where('payment_status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Find an order with its line items and the administrator who confirmed payment.
     */
    public function forAdmin(int $id): Order
    {
        return Order::query()->with(['items', 'paidBy:id,name'])->findOrFail($id);
    }

    /**
     * Record payment and queue its confirmation in the same database transaction.
     */
    public function confirmPayment(int $id, User $admin): void
    {
        DB::transaction(/** Lock the order and persist the payment and database job atomically. */ function () use ($id, $admin): void {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            if ($order->payment_status === PaymentStatus::Paid) {
                return;
            }

            $order->payment_status = PaymentStatus::Paid;
            $order->paid_at = now();
            $order->paidBy()->associate($admin);
            $order->save();

            Bus::dispatch((new SendPaymentConfirmation($order->id))->onConnection('database')->beforeCommit());
        }, 3);
    }

    /**
     * Build an order query limited to the guest cart for the given session key.
     *
     * @return Builder<Order>
     */
    private function guestOrders(string $key): Builder
    {
        return Order::query()->whereHas('cart', /** Match the cart that belongs to this guest session. */ fn ($query) => $query->where('session_key', $key));
    }
}
