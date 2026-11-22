<?php

namespace App\Http\Controllers;

use App\Http\Middleware\GuestCart;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GuestOrderController extends Controller
{
    /**
     * Set up access to the guest order service.
     */
    public function __construct(private OrderService $orders) {}

    /**
     * Show the order history for the current guest session.
     */
    public function index(Request $request): View
    {
        return view('orders.history', ['orders' => $this->orders->history($request->session()->get(GuestCart::KEY))]);
    }

    /**
     * Show a single order belonging to the current guest session.
     */
    public function show(Request $request, int $order): View
    {
        return view('orders.show', ['order' => $this->orders->forGuest($request->session()->get(GuestCart::KEY), $order)]);
    }
}
