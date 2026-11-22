<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Middleware\GuestCart;
use App\Jobs\SendPaymentConfirmation;
use App\Mail\PaymentConfirmed;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\AdminAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify catalog cache validation and public visibility after inventory changes.
     */
    public function test_product_changes_replace_the_browser_catalog_validator(): void
    {
        $product = $this->product();
        $first = $this->get('/')->assertOk()->assertSee($product->name);
        $etag = $first->headers->get('ETag');
        $this->assertNotNull($etag);
        $this->assertStringContainsString('no-cache', $first->headers->get('Cache-Control'));
        $this->assertEmpty($first->headers->getCookies());
        $this->get('/', ['If-None-Match' => $etag])->assertStatus(304);

        $this->get('/admin/products')->assertRedirect(route('admin.login'));
        $this->signInAdmin($this->admin());
        $this->get('/admin/products')->assertOk();

        $edit = [
            'name' => 'Izmenjen ranac',
            'description' => 'Opis',
            'price' => '5900,55',
            'stock' => 8,
            'is_active' => 1,
            'revision' => 1,
        ];

        $this->put(route('admin.products.update', $product->id), $edit)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'price_minor' => 590055, 'revision' => 2]);
        $this->get('/', ['If-None-Match' => $etag])->assertOk()->assertSee('Izmenjen ranac');
        $this->put(route('admin.products.update', $product->id), $edit)->assertSessionHasErrors('product');

        $this->delete(route('admin.products.destroy', $product->id))->assertRedirect(route('admin.products.index'));
        $this->get(route('products.show', $product->id))->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('Izmenjen ranac');
    }

    /**
     * Verify combined filters, literal wildcard search and invalid filter responses.
     */
    public function test_catalog_combines_search_price_and_availability(): void
    {
        $byName = $this->product(['name' => 'Termos', 'price_minor' => 129990]);
        $byDescription = $this->product(['name' => 'Boca', 'description' => 'Termos za put', 'price_minor' => 100000]);
        $soldOut = $this->product(['name' => 'Termos putni', 'stock' => 0, 'price_minor' => 100000]);
        $this->product(['name' => 'Termos jeftini', 'price_minor' => 99999]);
        $this->product(['name' => 'Termos skupi', 'price_minor' => 129991]);
        $this->product(['name' => 'Termos arhiviran', 'price_minor' => 100000, 'is_active' => false]);
        $this->product(['name' => 'Ranac', 'price_minor' => 100000]);
        $literal = $this->product(['name' => 'Oprema 100%']);

        $this->get('/?q=termOS&min_price=1000&max_price=1299,90&stock=available')
            ->assertOk()->assertViewHas('products', /** Expect matches from both product names and descriptions. */ fn ($products) => $products->pluck('id')->all() === [$byDescription->id, $byName->id]);
        $this->get('/?q=termos&stock=sold_out')
            ->assertOk()->assertViewHas('products', /** Expect only the sold-out match. */ fn ($products) => $products->pluck('id')->all() === [$soldOut->id]);
        $this->get('/?q=%25')
            ->assertOk()->assertViewHas('products', /** Expect only the product with a literal percent sign. */ fn ($products) => $products->pluck('id')->all() === [$literal->id]);
        $this->get('/?q=ništa')->assertOk()->assertSee('Nema proizvoda za izabrane kriterijume.');

        $invalid = $this->get('/?min_price=200&max_price=0')->assertUnprocessable()->assertSee('Najviša cena');
        $this->assertNull($invalid->headers->get('ETag'));
        $this->assertStringContainsString('no-store', $invalid->headers->get('Cache-Control'));
        $this->assertEmpty($invalid->headers->getCookies());
        $this->get('/?q%5B%5D=x')->assertUnprocessable();
    }

    /**
     * Verify filter preservation across pages and conditional catalog responses.
     */
    public function test_catalog_pagination_retains_filters_and_uses_a_conditional_response(): void
    {
        Product::query()->insert(array_map(/** Build one matching product row per index. */ fn (int $index) => [
            'name' => 'Termos '.$index,
            'description' => 'Opis',
            'price_minor' => 50000,
            'stock' => 2,
        ], range(1, 13)));
        $this->product();

        $url = '/?q=Termos&min_price=0&stock=available';
        $first = $this->get($url)->assertOk()
            ->assertViewHas('products', /** Expect a full first page and the filtered total. */ fn ($products) => $products->count() === 12 && $products->total() === 13)
            ->assertSee('q=Termos&amp;min_price=0&amp;stock=available&amp;page=2#oprema', false);
        $second = $this->get($url.'&page=2')->assertOk()
            ->assertViewHas('products', /** Expect the remaining match on the second page. */ fn ($products) => $products->count() === 1 && $products->total() === 13);

        $this->assertNotSame($first->headers->get('ETag'), $second->headers->get('ETag'));
        $this->get($url.'&page=2', ['If-None-Match' => $second->headers->get('ETag')])->assertStatus(304);
    }

    /**
     * Verify category filtering and the visibility of related products.
     */
    public function test_category_filters_catalog_and_recommends_only_other_active_products(): void
    {
        $lights = Category::query()->create(['name' => 'Rasveta']);
        $camping = Category::query()->create(['name' => 'Kampovanje']);
        $current = $this->product(['name' => 'Kamp fenjer', 'category_id' => $lights->id, 'stock' => 3]);
        $related = $this->product(['name' => 'Čeona lampa', 'category_id' => $lights->id]);
        $this->product(['name' => 'Arhivirana lampa', 'category_id' => $lights->id, 'is_active' => false]);
        $this->product(['name' => 'Šator', 'category_id' => $camping->id]);

        $this->get('/?category='.$lights->id.'&stock=available')->assertOk()
            ->assertSee('Na stanju: 3 kom.')
            ->assertViewHas('products', /** Expect available products from the selected category. */ fn ($products) => $products->pluck('id')->all() === [$related->id, $current->id]);

        $this->get(route('products.show', $current->id))->assertOk()
            ->assertSee('Rasveta')
            ->assertSee('Slični proizvodi')
            ->assertSee('Čeona lampa')
            ->assertDontSee('Arhivirana lampa')
            ->assertDontSee('Šator')
            ->assertViewHas('related', /** Expect only the other active product from the same category. */ fn ($products) => $products->pluck('id')->all() === [$related->id]);
    }

    /**
     * Verify that administrators can manage categories and product stock.
     */
    public function test_admin_can_create_a_category_and_set_category_and_stock_on_a_product(): void
    {
        $this->post(route('admin.categories.store'), ['name' => 'Rasveta'])->assertRedirect(route('admin.login'));
        $this->signInAdmin($this->admin());
        $this->post(route('admin.categories.store'), ['name' => 'Rasveta'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.categories.index'));
        $category = Category::query()->sole();
        $this->get(route('admin.products.create'))->assertOk()->assertSee('Rasveta');

        $data = [
            'name' => 'Kamp fenjer',
            'description' => 'Opis',
            'price' => '1690,00',
            'stock' => 7,
            'is_active' => 1,
        ];
        $this->post(route('admin.products.store'), $data + ['category_id' => $category->id + 1])
            ->assertSessionHasErrors('category_id');
        $this->post(route('admin.products.store'), $data + ['category_id' => $category->id])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Kamp fenjer', 'category_id' => $category->id, 'stock' => 7]);
        $this->get(route('admin.products.index'))->assertOk()->assertSee('Rasveta');
    }

    /**
     * Verify guest order history and idempotent payment confirmation.
     */
    public function test_checkout_stores_history_and_queues_only_one_payment_confirmation(): void
    {
        Mail::fake();
        $first = $this->product();
        $second = $this->product(['name' => 'Termos', 'price_minor' => 125050]);
        $admin = $this->admin();
        $this->get('/prijava')->assertNotFound();
        $this->get('/registracija')->assertNotFound();
        $this->get(route('products.show', $first->id))->assertOk()->assertSee('Dodaj u korpu');

        foreach ([[$first, 1], [$first, 2], [$second, 2]] as [$product, $quantity]) {
            $this->post(route('cart.store', $product->id), ['quantity' => $quantity])->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('cart_items', 2);
        $this->get(route('cart.index'))->assertOk()->assertSee($second->name);

        $checkout = [
            'expected_total_minor' => 1750100,
            'recipient_name' => 'Petar Kupac',
            'recipient_email' => 'PETAR@example.test',
            'phone' => '+381641234567',
            'address' => 'Terazije 1',
            'city' => 'Beograd',
            'postal_code' => '11000',
        ];
        $this->post(route('checkout.store'), ['expected_total_minor' => 100] + $checkout)->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->post(route('checkout.store'), $checkout)->assertSessionHasNoErrors();

        $buyer = User::query()->where('email', 'petar@example.test')->sole();
        $this->assertSame(Role::Customer, $buyer->role);
        $this->assertNull($buyer->password);
        $this->assertGuest();
        $order = $buyer->orders()->with('items')->sole();
        $this->assertSame(1750100, $order->total_minor);
        $this->assertCount(2, $order->items);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(7, $first->fresh()->stock);
        $this->assertSame(8, $second->fresh()->stock);
        $this->post(route('checkout.store'), $checkout)->assertSessionHasErrors('cart');

        $first->update(['name' => 'Novi naziv', 'price_minor' => 800000]);
        $this->get(route('orders.show', $order->id))->assertOk()->assertSee('Ranac')->assertDontSee('Novi naziv');
        $this->get(route('orders.history'))->assertOk()->assertSee('Čeka naplatu');
        $this->withSession([GuestCart::KEY => Str::uuid()->toString()]);
        $this->get(route('orders.show', $order->id))->assertNotFound();

        $this->signInAdmin($admin);
        $this->get(route('admin.orders.index', ['status' => 'pending']))->assertOk()->assertSee($buyer->email);
        $this->get(route('admin.orders.show', $order->id))->assertOk()->assertSee('Potvrdi primljenu uplatu');
        $this->post(route('admin.orders.confirm', $order->id))->assertRedirect();
        $this->post(route('admin.orders.confirm', $order->id))->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'paid_by' => $admin->id]);
        $this->assertDatabaseCount('jobs', 1);
        (new SendPaymentConfirmation($order->id))->handle();
        Mail::assertSent(PaymentConfirmed::class, /** Match the confirmation sent to the buyer. */ fn ($mail) => $mail->hasTo($buyer->email));
    }

    /**
     * Verify transaction rollback when a cart item is no longer available.
     */
    public function test_checkout_rolls_back_all_changes_if_any_item_loses_stock(): void
    {
        $first = $this->product();
        $second = $this->product(['name' => 'Podloga']);

        $this->post(route('cart.store', $first->id), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->post(route('cart.store', $second->id), ['quantity' => 5])->assertSessionHasNoErrors();
        $second->update(['stock' => 4]);

        $this->post(route('checkout.store'), [
            'expected_total_minor' => 4000000,
            'recipient_name' => 'Kupac',
            'recipient_email' => 'kupac@example.test',
            'phone' => '0641234567',
            'address' => 'Terazije 1',
            'city' => 'Beograd',
            'postal_code' => '11000',
        ])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('cart_items', 2);
        $this->assertSame(10, $first->fresh()->stock);
    }

    /**
     * Verify account creation permissions and administrator validation.
     */
    public function test_only_admins_can_create_users_and_admins(): void
    {
        $customerData = ['name' => 'Kupac', 'email' => 'KUPAC@example.test', 'role' => 'customer'];
        $adminData = ['name' => 'Novi admin', 'email' => 'novi@example.test', 'role' => 'admin'];

        $this->post(route('admin.users.store'), $customerData)->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('users', 0);

        $this->signInAdmin($this->admin());
        $this->get(route('admin.users.create'))->assertOk()->assertSee('Dodaj korisnika');
        $this->post(route('admin.users.store'), $customerData)->assertRedirect(route('admin.users.index'));
        $customer = User::query()->where('email', 'kupac@example.test')->sole();
        $this->assertSame(Role::Customer, $customer->role);
        $this->assertNull($customer->password);
        $this->post(route('admin.users.store'), $adminData)->assertSessionHasErrors('password');

        $this->post(route('admin.users.store'), $adminData + [
            'password' => 'lozinka-123',
            'password_confirmation' => 'lozinka-123',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Novi admin');

        $newAdmin = User::query()->where('email', 'novi@example.test')->sole();
        $this->assertSame(Role::Admin, $newAdmin->role);
        $this->assertTrue(Hash::check('lozinka-123', $newAdmin->password));
        $this->post(route('admin.login.store'), ['email' => $customer->email, 'password' => 'lozinka-123'])
            ->assertSessionHasErrors('email');
        $this->signInAdmin($newAdmin);
        $this->get(route('admin.users.index'))->assertOk();
    }

    /**
     * Create a product fixture with optional attribute overrides.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function product(array $attributes = []): Product
    {
        return Product::query()->create($attributes + [
            'name' => 'Ranac',
            'description' => 'Opis',
            'stock' => 10,
            'price_minor' => 500000,
            'is_active' => true,
        ]);
    }

    /**
     * Create an administrator fixture.
     */
    private function admin(): User
    {
        return User::factory()->create(['role' => Role::Admin, 'password' => 'lozinka-123']);
    }

    /**
     * Authenticate the supplied administrator through the login route.
     */
    private function signInAdmin(User $admin): void
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'lozinka-123',
        ])->assertRedirect(route('admin.products.index'))->assertCookie(AdminAuthService::COOKIE);

        $this->withCookie(AdminAuthService::COOKIE, $response->getCookie(AdminAuthService::COOKIE)->getValue());
    }
}
