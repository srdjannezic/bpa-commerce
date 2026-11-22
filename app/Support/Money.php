<?php

namespace App\Support;

final class Money
{
    /**
     * Convert a dinar amount written with a comma or dot to integer minor units.
     */
    public static function fromDecimal(string $amount): int
    {
        [$dinars, $fraction] = array_pad(explode('.', str_replace(',', '.', $amount), 2), 2, '');

        return (int) $dinars * 100 + (int) str_pad($fraction, 2, '0');
    }

    /**
     * Format a minor-unit amount for display in Serbian dinars.
     */
    public static function format(int $amount): string
    {
        return number_format($amount / 100, 2, ',', '.').' RSD';
    }

    /**
     * Render minor units as a dot-separated decimal with two fractional digits.
     */
    public static function decimal(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
