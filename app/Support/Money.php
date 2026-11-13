<?php

namespace App\Support;

/** Les montants sont stockés en millimes (entier) : 1 TND = 1 000 millimes. */
class Money
{
    public static function tnd(int $millimes): string
    {
        return number_format($millimes / 1000, 3, ',', ' ') . ' TND';
    }
}
