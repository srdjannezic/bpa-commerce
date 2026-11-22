<x-layout area="admin" title="Administracija porudžbina">
    <div class="page-heading"><h1>Porudžbine</h1></div>
    <nav class="filters" aria-label="Status naplate">
        <a @class(['button compact', 'secondary' => $status !== null]) href="{{ route('admin.orders.index') }}">Sve</a>
        @foreach (\App\Enums\PaymentStatus::cases() as $option)
            <a @class(['button compact', 'secondary' => $status !== $option]) href="{{ route('admin.orders.index', ['status' => $option->value]) }}">{{ $option->label() }}</a>
        @endforeach
    </nav>
    <section class="panel">
        @if ($orders->isEmpty())
            <p class="muted">Nema porudžbina za ovaj prikaz.</p>
        @else
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Porudžbina</th><th>Kupac</th><th>Datum</th><th>Stavke</th><th>Naplata</th><th class="amount">Ukupno</th></tr></thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><a class="text-link" href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a></td>
                                <td>{{ $order->user->name }}<br><small class="muted">{{ $order->user->email }}</small></td>
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
