<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date'  => 'date',
        'paid_date' => 'date',
    ];

    public function pawnItem()
    {
        return $this->belongsTo(PawnItem::class, 'pawn_item_id');
    }

    public function loanContract()
    {
        return $this->belongsTo(LoanContract::class, 'loan_contract_id');
    }
}