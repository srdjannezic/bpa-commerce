<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $filter = $request->query('status');
        $status = is_string($filter) ? PaymentStatus::tryFrom($filter) : null;

        return view('admin.orders.index', ['orders' => $this->orders->all($status), 'status' => $status]);
    }

    public function show(int $order): View
    {
        return view('admin.orders.show', ['order' => $this->orders->forAdmin($order)]);
    }

    public function confirm(Request $request, int $order): RedirectResponse
    {
        $this->orders->confirmPayment($order, $request->attributes->get('admin'));

        return to_route('admin.orders.show', $order)->with('status', 'Naplata je potvrđena.');
    }
}
