<?php

namespace App\Http\Controllers;

use App\Http\Middleware\GuestCart;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GuestOrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        return view('orders.history', ['orders' => $this->orders->history($request->session()->get(GuestCart::KEY))]);
    }

    public function show(Request $request, int $order): View
    {
        return view('orders.show', ['order' => $this->orders->forGuest($request->session()->get(GuestCart::KEY), $order)]);
    }
}
