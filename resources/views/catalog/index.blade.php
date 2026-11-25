<x-layout :static-catalog="true">
    <section class="hero">
        <div class="wrap hero-inner">
            <div class="hero-copy">
                <p class="eyebrow">Oprema za boravak napolju</p>
                <h1>Vidimo se<br> na stazi.</h1>
                <p class="hero-description">Od prvog koraka do poslednjeg uspona. Ponesi opremu koja prati tvoj tempo.</p>
                <a class="button hero-button" href="#oprema">Pogledaj opremu</a>
            </div>
        </div>
        <div class="hero-photo" role="img" aria-label="Vrhovi Durmitora iznad četinarske šume"></div>
    </section>

    <section class="wrap catalog-section" id="oprema">
        <div class="section-heading">
            <h2>Oprema za tvoju stazu</h2>
            @isset ($products)
                <span>{{ $products->total() }} {{ $products->total() === 1 ? 'proizvod' : 'proizvoda' }}</span>
            @endisset
        </div>

        <form class="catalog-filters" role="search" aria-label="Pretraga kataloga" action="{{ route('catalog') }}#oprema" method="get">
            <label class="catalog-search">Pretraga
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Naziv ili opis proizvoda" maxlength="100">
            </label>
            <label class="catalog-category">Kategorija
                <select name="category">
                    <option value="">Sve kategorije</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @selected((string) ($filters['category'] ?? '') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Najniža cena (RSD)
                <input type="text" inputmode="decimal" name="min_price" value="{{ $filters['min_price'] ?? '' }}" maxlength="10">
            </label>
            <label>Najviša cena (RSD)
                <input type="text" inputmode="decimal" name="max_price" value="{{ $filters['max_price'] ?? '' }}" maxlength="10">
            </label>
            <label class="catalog-stock">Dostupnost
                <select name="stock">
                    <option value="">Svi proizvodi</option>
                    <option value="available" @selected(($filters['stock'] ?? null) === 'available')>Na stanju</option>
                    <option value="sold_out" @selected(($filters['stock'] ?? null) === 'sold_out')>Rasprodato</option>
                </select>
            </label>
            <div class="catalog-filter-actions">
                <button class="button" type="submit">Primeni</button>
                @if ($filters || !empty($filterErrors))
                    <a class="button secondary" href="{{ route('catalog') }}#oprema">Poništi</a>
                @endif
            </div>
        </form>

        @if (!empty($filterErrors))
            <div class="notice error" role="alert">
                <ul>
                    @foreach ($filterErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @isset ($products)
            <div class="product-grid">
                @forelse ($products as $product)
                    <x-product-card :product="$product" :eager="$loop->index < 4" />
                @empty
                    <p class="empty catalog-empty">{{ $filters ? 'Nema proizvoda za izabrane kriterijume.' : 'Trenutno nema proizvoda u ponudi.' }}</p>
                @endforelse
            </div>
            <x-pagination :items="$products->fragment('oprema')" />
        @endisset
    </section>
</x-layout>
