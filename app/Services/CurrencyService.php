<?php
namespace App\Services;

class CurrencyService
{
    const RATES = [
        'USD' => [
            'EUR' => 0.98,
            //'GBP' => 0.60,
        ],
    ];

    public function convert(float $amount, string $currencyFrom, string $currencyTo): float
    {
        $rate = self::RATES[$currencyFrom][$currencyTo] ?? 0;

        return round($amount * $rate, 2);
    }
}
