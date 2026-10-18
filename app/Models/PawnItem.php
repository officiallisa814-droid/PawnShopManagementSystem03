<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawnItem extends Model
{
    use HasFactory;

    // ប្រាប់ឈ្មោះតារាងឱ្យចំទៅកាន់ Database
    protected $table = 'pawn_items';

    /**
     * ប្តូរមកប្រើប្រាស់ $guarded ទទេរបែបនេះវិញ
     * ដើម្បីបើកសិទ្ធិឱ្យរាល់គ្រប់ជួរ columns ទាំងអស់ (រួមទាំង customer_id) 
     * អាចបញ្ចូលទិន្នន័យបានភ្លាមៗ ដោយគ្មានការទប់ស្កាត់ពី Laravel ទៀតឡើយ។
     */
    protected $guarded = [];

    // មុខងារភ្ជាប់ទៅកាន់តារាងអតិថិជន
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // បន្ថែមមុខងារនេះទៅក្នុង Class PawnItem ដើម្បីភ្ជាប់ទំនាក់ទំនងទៅកាន់តារាងបង់ប្រាក់
    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class, 'pawn_item_id');
    }

    public function loanContracts()
    {
        return $this->hasMany(LoanContract::class, 'pawn_item_id');
    }

    /** តើទំនិញនេះមានកិច្ចសន្យាបើកអត់? */
    public function hasOpenContract(): bool
    {
        return $this->loanContracts()->whereIn('status', LoanContract::OPEN)->exists();
    }

    public function photos()
    {
        return $this->hasMany(PawnItemPhoto::class, 'pawn_item_id');
    }
}
