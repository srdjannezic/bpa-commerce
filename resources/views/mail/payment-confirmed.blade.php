<!DOCTYPE html>
<html lang="sr-Latn">
<body>
    <p>Zdravo, {{ $order->recipient_name }}.</p>
    <p>Uspešno je primljena uplata od <strong>{{ \App\Support\Money::format($order->total_minor) }}</strong>
        za porudžbinu <strong>#{{ $order->id }}</strong>, plaćenu pouzećem.</p>
    <p>Hvala na kupovini!<br>Tim {{ config('app.name') }}</p>
</body>
</html>
