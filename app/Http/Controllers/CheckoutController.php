<?php

namespace App\Http\Controllers;

use App\Http\Middleware\GuestCart;
use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    /**
     * Set up the service that places orders.
     */
    public function __construct(private CheckoutService $checkout) {}

    /**
     * Place a cash-on-delivery order for the current guest cart.
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $delivery = $request->safe()->except('expected_total_minor');
        $order = $this->checkout->place($request->session()->get(GuestCart::KEY), $delivery, (int) $request->validated('expected_total_minor'));

        return to_route('orders.show', $order->id)->with('status', 'Porudžbina je primljena. Plaćaš prilikom dostave.');
    }
}
