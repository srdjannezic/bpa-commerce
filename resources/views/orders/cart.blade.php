<x-layout title="Korpa">
    <div class="page-heading">
        <h1>Korpa</h1>
        <a class="text-link" href="{{ route('catalog') }}#oprema">Nastavi kupovinu</a>
    </div>
    @if ($items->isEmpty())
        <div class="empty">Korpa je prazna.</div>
    @else
        <div class="checkout-grid">
            <section class="panel">
                <h2>Stavke</h2>
                @foreach ($items as $item)
                    @php($product = $item->product)
                    <article class="cart-row">
                        <x-product-picture :product="$product" class="cart-picture" />
                        <div>
                            <h3>{{ $product->name }}</h3>
                            @if (! $product->is_active || $item->quantity > $product->stock)
                                <p class="stock unavailable">Dostupno {{ $product->is_active ? $product->stock : 0 }}</p>
                            @endif
                            <p class="muted">{{ \App\Support\Money::format($product->price_minor) }} po komadu</p>
                            <div class="row-actions">
                                @if ($product->is_active && $product->stock > 0)
                                    <form class="qty-form" action="{{ route('cart.update', $item->id) }}" method="post">
                                        @csrf
                                        @method('PATCH')
                                        <label class="visually-hidden" for="qty-{{ $item->id }}">Količina za {{ $product->name }}</label>
                                        <input id="qty-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ min(\App\Services\CartService::MAX_QUANTITY, $product->stock) }}" value="{{ $item->quantity }}" required>
                                        <button class="button secondary compact" type="submit">Sačuvaj</button>
                                    </form>
                                @else
                                    <span>{{ $item->quantity }} kom.</span>
                                @endif
                                <form action="{{ route('cart.destroy', $item->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="plain-button danger" type="submit" aria-label="Ukloni {{ $product->name }}">Ukloni</button>
                                </form>
                            </div>
                        </div>
                        <strong class="cart-price">{{ \App\Support\Money::format($item->quantity * $product->price_minor) }}</strong>
                    </article>
                @endforeach
                <div class="total"><span>Ukupno</span><strong>{{ \App\Support\Money::format($total) }}</strong></div>
            </section>

            <section class="form-panel">
                <h2>Podaci za dostavu</h2>
                <p class="muted">Plaćanje pouzećem pri dostavi.</p>
                @if ($ready)
                    <form method="post" action="{{ route('checkout.store') }}" class="fields">
                        @csrf
                        <input type="hidden" name="expected_total_minor" value="{{ $total }}">
                        <label>Ime i prezime<input name="recipient_name" autocomplete="name" value="{{ old('recipient_name') }}" maxlength="120" required></label>
                        <label>Email za potvrdu uplate<input name="recipient_email" type="email" autocomplete="email" value="{{ old('recipient_email') }}" maxlength="255" required></label>
                        <label>Telefon<input name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" maxlength="30" required></label>
                        <label>Ulica i broj<input name="address" autocomplete="street-address" value="{{ old('address') }}" maxlength="150" required></label>
                        <div class="field-pair">
                            <label>Mesto<input name="city" autocomplete="address-level2" value="{{ old('city') }}" maxlength="100" required></label>
                            <label>Poštanski broj<input name="postal_code" autocomplete="postal-code" inputmode="numeric" pattern="[0-9]{5}" value="{{ old('postal_code') }}" maxlength="5" required></label>
                        </div>
                        <button class="button" type="submit">Potvrdi porudžbinu</button>
                    </form>
                @else
                    <p class="notice">Prilagodi količinu ili ukloni nedostupne stavke da nastaviš.</p>
                @endif
            </section>
        </div>
    @endif
</x-layout>
