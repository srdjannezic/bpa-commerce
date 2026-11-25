<?php

namespace App\Support;

final class Money
{
    public static function fromDecimal(string $amount): int
    {
        [$dinars, $fraction] = array_pad(explode('.', str_replace(',', '.', $amount), 2), 2, '');

        return (int) $dinars * 100 + (int) str_pad($fraction, 2, '0');
    }

    public static function format(int $amount): string
    {
        return number_format($amount / 100, 2, ',', '.').' RSD';
    }

    public static function decimal(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
