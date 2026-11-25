<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentConfirmed extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Potvrda uplate za porudžbinu #{$this->order->id}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payment-confirmed');
    }
}
