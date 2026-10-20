<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'loan_contract_id', 'pawn_item_id', 'scheduled_date', 'starting_price', 'status',
        'buyer_name', 'buyer_phone', 'sale_price', 'owed_amount', 'sold_date', 'note',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'sold_date'      => 'date',
        'starting_price' => 'float',
        'sale_price'     => 'float',
        'owed_amount'    => 'float',
    ];

    public function contract()
    {
        return $this->belongsTo(LoanContract::class, 'loan_contract_id');
    }
}