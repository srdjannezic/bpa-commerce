<x-layout area="admin" title="Administracija proizvoda">
    <div class="page-heading">
        <h1>Proizvodi</h1>
        <a class="button" href="{{ route('admin.products.create') }}">Dodaj proizvod</a>
    </div>
    <section class="panel">
        @if ($products->isEmpty())
            <p class="muted">Još nema proizvoda.</p>
        @else
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Proizvod</th><th>Kategorija</th><th class="amount">Cena</th><th>Zaliha</th><th>Status</th><th>Radnje</th></tr></thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <th scope="row">{{ $product->name }}</th>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td class="amount">{{ \App\Support\Money::format($product->price_minor) }}</td>
                                <td>{{ $product->stock }}</td>
                                <td><span @class(['pill', 'paid' => $product->is_active])>{{ $product->is_active ? 'Aktivan' : 'Arhiviran' }}</span></td>
                                <td><div class="row-actions">
                                    <a class="text-link" href="{{ route('admin.products.edit', $product->id) }}">Izmeni</a>
                                    @if ($product->is_active)
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button class="plain-button danger" type="submit">Arhiviraj</button>
                                        </form>
                                    @endif
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :items="$products" />
        @endif
    </section>
</x-layout>
