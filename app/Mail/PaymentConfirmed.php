<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentConfirmed extends Mailable
{
    /**
     * Expose the paid order to the confirmation template.
     */
    public function __construct(public Order $order) {}

    /**
     * Include the order number in the email subject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: "Potvrda uplate za porudžbinu #{$this->order->id}");
    }

    /**
     * Select the payment confirmation email template.
     */
    public function content(): Content
    {
        return new Content(view: 'mail.payment-confirmed');
    }
}
