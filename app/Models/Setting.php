<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['key', 'value', 'updated_at'];

    public const DEFAULTS = [
        'shop_name' => 'Pawn Shop',
        'shop_phone' => '',
        'shop_email' => '',
        'shop_address' => '',
        'default_rate' => '3',
        'default_term' => '1',
        'max_loan_percent' => '70',
        'tax_enabled' => '0',
        'tax_name' => 'VAT',
        'tax_rate' => '10',
        'currency_symbol' => '$',
        'currency_code' => 'USD',
        'khr_rate' => '4100',
    ];

    public static function all_(): array
    {
        $db = Schema::hasTable('settings') ? static::pluck('value', 'key')->all() : [];
        return array_merge(self::DEFAULTS, $db);
    }

    public static function get(string $key, $default = null)
    {
        return static::all_()[$key] ?? $default;
    }

    public static function put(array $pairs): void
    {
        foreach ($pairs as $k => $v) {
            static::updateOrCreate(['key' => $k], ['value' => (string) $v, 'updated_at' => now()]);
        }
    }
}