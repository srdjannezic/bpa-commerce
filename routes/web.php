<?php

use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\CategoryController as AdminCategories;
use App\Http\Controllers\Admin\OrderController as AdminOrders;
use App\Http\Controllers\Admin\ProductController as AdminProducts;
use App\Http\Controllers\Admin\UserController as AdminUsers;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GuestOrderController;
use App\Http\Middleware\AdminJwt;
use App\Http\Middleware\GuestCart;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::pattern('product', '[0-9]{1,18}');
Route::pattern('item', '[0-9]{1,18}');
Route::pattern('order', '[0-9]{1,18}');

Route::get('/', [CatalogController::class, 'index'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
    ->name('catalog');
Route::middleware(GuestCart::class)->group(/** Register storefront routes that use a guest cart session. */ function (): void {
    Route::get('/proizvodi/{product}', [CatalogController::class, 'show'])->block()->name('products.show');
    Route::get('/porudzbine', [GuestOrderController::class, 'index'])->block()->name('orders.history');
    Route::get('/porudzbine/{order}', [GuestOrderController::class, 'show'])->block()->name('orders.show');
    Route::get('/korpa', [CartController::class, 'index'])->block()->name('cart.index');
    Route::post('/korpa/proizvod/{product}', [CartController::class, 'store'])->block()->name('cart.store');
    Route::patch('/korpa/{item}', [CartController::class, 'update'])->block()->name('cart.update');
    Route::delete('/korpa/{item}', [CartController::class, 'destroy'])->block()->name('cart.destroy');
    Route::post('/porudzbine', [CheckoutController::class, 'store'])->block()->name('checkout.store');
});

Route::prefix('admin')->name('admin.')->group(/** Register routes under the admin prefix. */ function (): void {
    Route::redirect('/', '/admin/products')->name('home');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AdminAuth::class, 'login'])->middleware('throttle:8,1')->name('login.store');

    Route::middleware(AdminJwt::class)->group(/** Register admin routes that require authentication. */ function (): void {
        Route::post('/logout', [AdminAuth::class, 'logout'])->name('logout');
        Route::resource('products', AdminProducts::class)->except('show');
        Route::resource('categories', AdminCategories::class)->only(['index', 'store']);
        Route::resource('users', AdminUsers::class)->only(['index', 'create', 'store']);
        Route::get('/orders', [AdminOrders::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrders::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/payment', [AdminOrders::class, 'confirm'])->name('orders.confirm');
    });
});
