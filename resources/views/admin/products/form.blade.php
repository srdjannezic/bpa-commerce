<x-layout area="admin" :title="$product ? 'Izmeni proizvod' : 'Dodaj proizvod'">
    <a class="back-link" href="{{ route('admin.products.index') }}">Svi proizvodi</a>
    <section class="form-panel edit-panel">
        <h1>{{ $product ? 'Izmeni proizvod' : 'Dodaj proizvod' }}</h1>
        <form method="post" action="{{ $product ? route('admin.products.update', $product->id) : route('admin.products.store') }}" class="fields">
            @csrf
            @if ($product)
                @method('PUT')
                <input type="hidden" name="revision" value="{{ old('revision', $product->revision) }}">
            @endif
            <label>Naziv<input name="name" value="{{ old('name', $product?->name) }}" maxlength="120" required autofocus></label>
            <label>Kategorija
                <select name="category_id">
                    <option value="">Bez kategorije</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('category_id', $product?->category_id) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Opis<textarea name="description" rows="5" maxlength="4000" required>{{ old('description', $product?->description) }}</textarea></label>
            <div class="field-pair">
                <label>Cena (RSD)<input name="price" inputmode="decimal" value="{{ old('price', $product ? \App\Support\Money::decimal($product->price_minor) : '') }}" required></label>
                <label>Zaliha (kom.)<input name="stock" type="number" min="0" max="1000000" value="{{ old('stock', $product?->stock ?? 0) }}" required></label>
            </div>
            <label>Adresa fotografije (opciono)<input name="image_url" inputmode="url" placeholder="https://…" value="{{ old('image_url', $product?->image_url) }}" maxlength="500"></label>
            <label class="checkbox">
                <input name="is_active" type="hidden" value="0">
                <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $product?->is_active ?? true))>
                Prikaži u prodavnici
            </label>
            @if ($errors->has('product') && $product)
                <a class="button secondary" href="{{ route('admin.products.edit', $product->id) }}">Učitaj aktuelnu verziju</a>
            @else
                <button class="button" type="submit">Sačuvaj proizvod</button>
            @endif
        </form>
    </section>
</x-layout>
