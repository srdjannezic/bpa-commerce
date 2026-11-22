<x-layout :title="'Porudžbina #'.$order->id">
    <a class="back-link" href="{{ route('orders.history') }}">Istorija porudžbina</a>
    <div class="page-heading"><h1>Porudžbina #{{ $order->id }}</h1></div>
    <x-order-summary :order="$order" />
</x-layout>
