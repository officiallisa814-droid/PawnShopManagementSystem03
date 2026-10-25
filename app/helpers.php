<?php

use App\Models\Setting;

if (!function_exists('pawn_money')) {
    /** pawn_money(100) => $100.00 ; pawn_money(100, true) => 410,000 ៛ */
    function pawn_money(float $v, bool $khr = false): string
    {
        $s = Setting::all_();
        if ($khr) {
            return number_format($v * (float) $s['khr_rate']) . ' ៛';
        }
        return $s['currency_symbol'] . number_format($v, 2);
    }
}

if (!function_exists('pawn_tax')) {
    function pawn_tax(float $amount): float
    {
        $s = Setting::all_();
        return $s['tax_enabled'] === '1' ? round($amount * (float) $s['tax_rate'] / 100, 2) : 0.0;
    }
}