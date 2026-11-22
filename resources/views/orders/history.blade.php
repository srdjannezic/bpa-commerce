<x-layout title="Moje porudžbine">
    <div class="page-heading">
        <div><h1>Moje porudžbine</h1><p class="muted">Porudžbine iz ove sesije pregledača.</p></div>
    </div>
    <section class="panel">
        <h2>Istorija porudžbina</h2>
        @if ($orders->isEmpty())
            <p class="muted">Još nemaš porudžbina. <a class="text-link" href="{{ route('catalog') }}#oprema">Pogledaj opremu</a></p>
        @else
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Porudžbina</th><th>Datum</th><th>Stavke</th><th>Naplata</th><th class="amount">Ukupno</th></tr></thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><a class="text-link" href="{{ route('orders.show', $order->id) }}">#{{ $order->id }}</a></td>
                                <td>{{ $order->created_at->timezone('Europe/Belgrade')->format('d.m.Y. H:i') }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td><x-payment-state :state="$order->payment_status" /></td>
                                <td class="amount">{{ \App\Support\Money::format($order->total_minor) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :items="$orders" />
        @endif
    </section>
</x-layout>
