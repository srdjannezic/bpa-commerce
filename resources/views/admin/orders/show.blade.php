<x-layout area="admin" :title="'Porudžbina #'.$order->id">
    <a class="back-link" href="{{ route('admin.orders.index') }}">Sve porudžbine</a>
    <div class="page-heading"><h1>Porudžbina #{{ $order->id }}</h1></div>
    <x-order-summary :order="$order" :admin="true">
        @if ($order->payment_status === \App\Enums\PaymentStatus::Pending)
            <form action="{{ route('admin.orders.confirm', $order->id) }}" method="post">
                @csrf
                <button class="button" type="submit">Potvrdi primljenu uplatu</button>
            </form>
        @endif
    </x-order-summary>
</x-layout>
