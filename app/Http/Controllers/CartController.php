<?php

namespace App\Http\Controllers;

use App\Http\Middleware\GuestCart;
use App\Http\Requests\CartQuantityRequest;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request): View
    {
        return view('orders.cart', $this->cart->summary($request->session()->get(GuestCart::KEY)));
    }

    public function store(CartQuantityRequest $request, int $product): RedirectResponse
    {
        $this->cart->add($request->session()->get(GuestCart::KEY), $product, (int) $request->validated('quantity'));

        return to_route('cart.index')->with('status', 'Proizvod je dodat u korpu.');
    }

    public function update(CartQuantityRequest $request, int $item): RedirectResponse
    {
        $this->cart->update($request->session()->get(GuestCart::KEY), $item, (int) $request->validated('quantity'));

        return to_route('cart.index')->with('status', 'Količina je sačuvana.');
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $this->cart->remove($request->session()->get(GuestCart::KEY), $item);

        return to_route('cart.index')->with('status', 'Proizvod je uklonjen iz korpe.');
    }
}
