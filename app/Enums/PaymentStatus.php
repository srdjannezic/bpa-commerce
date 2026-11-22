<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';

    /**
     * Return the Serbian label shown for this payment status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Čeka naplatu',
            self::Paid => 'Plaćeno',
        };
    }
}
