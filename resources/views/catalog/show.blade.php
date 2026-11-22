<x-layout :title="$product->name">
    <a class="back-link" href="{{ route('catalog') }}#oprema">Svi proizvodi</a>
    <div class="detail-grid">
        <x-product-picture :product="$product" :eager="true" />
        <section class="detail-content">
            @if ($product->category)
                <a class="text-link detail-category" href="{{ route('catalog', ['category' => $product->category_id]) }}#oprema">{{ $product->category->name }}</a>
            @endif
            <x-product-stock :stock="$product->stock" />
            <h1>{{ $product->name }}</h1>
            <p class="detail-price">{{ \App\Support\Money::format($product->price_minor) }}</p>
            <p class="description">{{ $product->description }}</p>
            @if ($product->stock)
                <form action="{{ route('cart.store', $product->id) }}" method="post" class="detail-form">
                    @csrf
                    <label>Količina<input name="quantity" type="number" min="1" max="{{ min(\App\Services\CartService::MAX_QUANTITY, $product->stock) }}" value="{{ old('quantity', 1) }}" required></label>
                    <button class="button" type="submit">Dodaj u korpu</button>
                </form>
            @endif
            <p class="muted detail-note">Plaćanje pouzećem pri dostavi.</p>
        </section>
    </div>
    @if ($related->isNotEmpty())
        <section class="related-products" aria-labelledby="related-title">
            <div class="section-heading">
                <h2 id="related-title">Slični proizvodi</h2>
                <a class="text-link" href="{{ route('catalog', ['category' => $product->category_id]) }}#oprema">Cela kategorija</a>
            </div>
            <div class="product-grid">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
