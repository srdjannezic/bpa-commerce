<?php

namespace App\Jobs;

use App\Mail\PaymentConfirmed;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public array $backoff = [15, 60, 300];

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::query()->findOrFail($this->orderId);

        Mail::to($order->recipient_email)->send(new PaymentConfirmed($order));
    }
}
