<?php

namespace App\Http\Controllers;

use App\Http\Middleware\GuestCart;
use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout) {}

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $delivery = $request->safe()->except('expected_total_minor');
        $order = $this->checkout->place($request->session()->get(GuestCart::KEY), $delivery, (int) $request->validated('expected_total_minor'));

        return to_route('orders.show', $order->id)->with('status', 'Porudžbina je primljena. Plaćaš prilikom dostave.');
    }
}
