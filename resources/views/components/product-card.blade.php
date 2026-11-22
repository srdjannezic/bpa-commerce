@props(['product', 'eager' => false])
<article class="product-card">
    <a href="{{ route('products.show', $product->id) }}">
        <x-product-picture :product="$product" :eager="$eager" />
        <div class="product-summary">
            <x-product-stock :stock="$product->stock" />
            <h3>{{ $product->name }}</h3>
            <p class="price">{{ \App\Support\Money::format($product->price_minor) }}</p>
        </div>
    </a>
</article>
