<?php

declare(strict_types=1);

namespace App\Helpers;

class Formatter
{
    public static function money(float|string $amount, ?string $currency = null): string
    {
        $currency = $currency ?? self::currency();
        return $currency . ' ' . number_format((float) $amount, 2);
    }

    public static function currency(): string
    {
        static $currency = null;
        if ($currency === null) {
            try {
                $settings = new \App\Models\Settings(\App\Helpers\Database::getInstance());
                $currency = $settings->get('currency', 'USD');
            } catch (\Throwable) {
                $currency = 'USD';
            }
        }
        return $currency;
    }

    public static function date(?string $datetime, string $format = 'M j, Y'): string
    {
        if ($datetime === null || $datetime === '') {
            return '—';
        }
        return date($format, strtotime($datetime));
    }

    public static function datetime(?string $datetime): string
    {
        return self::date($datetime, 'M j, Y g:i A');
    }
}
