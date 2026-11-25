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
    public function history(string $key): LengthAwarePaginator
    {
        return $this->guestOrders($key)->withCount('items')->latest('id')->paginate(15);
    }

    public function forGuest(string $key, int $id): Order
    {
        return $this->guestOrders($key)->with('items')->findOrFail($id);
    }

    public function all(?PaymentStatus $status): LengthAwarePaginator
    {
        return Order::query()
            ->with('user:id,name,email')
            ->withCount('items')
            ->when($status, fn ($query) => $query->where('payment_status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function forAdmin(int $id): Order
    {
        return Order::query()->with(['items', 'paidBy:id,name'])->findOrFail($id);
    }

    public function confirmPayment(int $id, User $admin): void
    {
        DB::transaction(function () use ($id, $admin): void {
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

    private function guestOrders(string $key): Builder
    {
        return Order::query()->whereHas('cart', fn ($query) => $query->where('session_key', $key));
    }
}
