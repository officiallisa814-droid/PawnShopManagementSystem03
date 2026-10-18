<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_name',
        'gender',
        'dob',
        'passport_id',
        'phone_number',
        'email',
        'address_house',
        'address_street',
        'address_village',
        'address_district',
        'address_province',
        'guarantor_name',
        'guarantor_phone',
        'active_pledges',
        'customer_type',
        'customer_status',
        'id_card_photo',
        'customer_avatar',
        'notes',
    ];

    public function pawnItems()
    {
        return $this->hasMany(PawnItem::class, 'customer_id');
    }

    public function loanContracts()
    {
        return $this->hasMany(LoanContract::class, 'customer_id');
    }
    protected $casts = [
        'dob' => 'date',
        'active_pledges' => 'integer',
    ];

}
