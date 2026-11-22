@props(['order', 'admin' => false])
<div class="checkout-grid">
    <section class="panel">
        <h2>Stavke porudžbine</h2>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Proizvod</th><th>Količina</th><th class="amount">Cena po komadu</th><th class="amount">Iznos</th></tr></thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="amount">{{ \App\Support\Money::format($item->unit_price_minor) }}</td>
                            <td class="amount">{{ \App\Support\Money::format($item->quantity * $item->unit_price_minor) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="total"><span>Ukupno</span><strong>{{ \App\Support\Money::format($order->total_minor) }}</strong></div>
    </section>
    <section class="panel">
        <h2>Dostava i naplata</h2>
        <div class="shipping">
            <strong>{{ $order->recipient_name }}</strong>
            <span>{{ $order->address }}</span><span>{{ $order->postal_code }} {{ $order->city }}</span>
            <span>{{ $order->phone }}</span><span>{{ $order->recipient_email }}</span>
        </div>
        <p>Pouzećem · <x-payment-state :state="$order->payment_status" /></p>
        @if ($order->paid_at)
            <p class="muted">Naplata {{ $order->paid_at->timezone('Europe/Belgrade')->format('d.m.Y. H:i') }}
                @if ($admin && $order->paidBy) · {{ $order->paidBy->name }} @endif
            </p>
        @endif
        {{ $slot }}
    </section>
</div>
