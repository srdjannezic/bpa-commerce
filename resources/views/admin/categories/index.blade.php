<x-layout area="admin" title="Kategorije">
    <div class="page-heading"><h1>Kategorije</h1></div>
    <div class="checkout-grid">
        <section class="panel">
            <h2>Postojeće kategorije</h2>
            @if (empty($categories))
                <p class="muted">Još nema kategorija.</p>
            @else
                <ul class="category-list">
                    @foreach ($categories as $id => $name)
                        <li><a class="text-link" href="{{ route('catalog', ['category' => $id]) }}#oprema">{{ $name }}</a></li>
                    @endforeach
                </ul>
            @endif
        </section>
        <section class="form-panel">
            <h2>Dodaj kategoriju</h2>
            <form action="{{ route('admin.categories.store') }}" method="post" class="fields">
                @csrf
                <label>Naziv<input name="name" value="{{ old('name') }}" required maxlength="80"></label>
                <button class="button" type="submit">Sačuvaj kategoriju</button>
            </form>
        </section>
    </div>
</x-layout>
