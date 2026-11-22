@props(['state'])
<span @class(['pill', 'paid' => $state === \App\Enums\PaymentStatus::Paid])>{{ $state->label() }}</span>
