<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTransaction extends Model
{
    protected $fillable = ['loan_contract_id', 'type', 'amount', 'date', 'note'];

    protected $casts = ['date' => 'date', 'amount' => 'float'];

    public function contract()
    {
        return $this->belongsTo(LoanContract::class, 'loan_contract_id');
    }
}