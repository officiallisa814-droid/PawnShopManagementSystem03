<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @property Carbon $loan_date
 * @property Carbon $due_date
 * @property Carbon $interest_from
 * @property Carbon|null $closed_at
 */
class LoanContract extends Model
{
    public const ACTIVE = 'active';
    public const RENEWED = 'renewed';
    public const REDEEMED = 'redeemed';
    public const EXPIRED = 'expired';
    public const AUCTIONED = 'auctioned';

    public const OPEN = [self::ACTIVE, self::RENEWED, self::EXPIRED];

    protected $fillable = [
        'contract_no',
        'customer_id',
        'pawn_item_id',
        'item_name',
        'item_description',
        'item_value',
        'loan_amount',
        'interest_rate',
        'term_months',
        'loan_date',
        'due_date',
        'interest_from',
        'status',
        'closed_at',
        'note',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'interest_from' => 'date',
        'closed_at' => 'date',
        'loan_amount' => 'float',
        'item_value' => 'float',
        'interest_rate' => 'float',
    ];

    protected $appends = ['months_elapsed', 'accrued_interest', 'total_due', 'days_overdue', 'is_open'];

    /* ---------- Relationships ---------- */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pawnItem()
    {
        return $this->belongsTo(PawnItem::class, 'pawn_item_id');
    }

    public function transactions()
    {
        return $this->hasMany(ContractTransaction::class)->latest('date');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class, 'loan_contract_id');
    }

    /* ---------- Helpers ---------- */

    /** PW-202610-0001 */
    public static function generateNumber(): string
    {
        $prefix = 'PW-' . now()->format('Ym') . '-';
        $last = static::where('contract_no', 'like', $prefix . '%')->max('contract_no');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /** កិច្ចសន្យាដែលហួសថ្ងៃកំណត់ ប្ដូរទៅ Expired */
    public static function markExpired(): void
    {
        static::whereIn('status', [self::ACTIVE, self::RENEWED])
            ->whereDate('due_date', '<', today())
            ->update(['status' => self::EXPIRED]);
    }

    public function getIsOpenAttribute(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    /** រាល់ 30 ថ្ងៃដែលចាប់ផ្ដើម = 1 ខែ (អប្បបរមា 1 ខែ) */
    public function getMonthsElapsedAttribute(): int
    {
        $end = $this->closed_at ?? today();
        $days = max(0, (int) $this->interest_from->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()));

        return max(1, (int) ceil($days / 30));
    }

    /** ការប្រាក់ = ប្រាក់ដើម × អត្រា% ក្នុងមួយខែ × ចំនួនខែ */
    public function getAccruedInterestAttribute(): float
    {
        return round($this->loan_amount * ($this->interest_rate / 100) * $this->months_elapsed, 2);
    }

    public function getTotalDueAttribute(): float
    {
        return round($this->loan_amount + $this->accrued_interest, 2);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_open || !$this->due_date->isPast() || $this->due_date->isToday()) {
            return 0;
        }

        return (int) $this->due_date->copy()->startOfDay()->diffInDays(today());
    }

    /** បង្កើតកាលវិភាគការប្រាក់ប្រចាំខែ */
    public function buildSchedule($from = null, ?int $months = null): void
    {
        $from = $from ? Carbon::parse($from) : $this->loan_date;
        $months = $months ?? (int) $this->term_months;
        $monthly = round($this->loan_amount * $this->interest_rate / 100, 2);
        $stamps = Schema::hasColumn('payment_schedules', 'created_at')
            ? ['created_at' => now(), 'updated_at' => now()] : [];

        for ($i = 1; $i <= max(1, $months); $i++) {
            DB::table('payment_schedules')->insert([
                'loan_contract_id' => $this->id,
                'pawn_item_id' => $this->pawn_item_id,
                'term_number' => $i,
                'due_date' => $from->copy()->addMonthsNoOverflow($i)->toDateString(),
                'interest_amount' => $monthly,
                'status' => 'unpaid',
            ] + $stamps);
        }
    }

    /** លុបកាលវិភាគដែលមិនទាន់បង់ */
    public function clearUnpaidSchedules(): void
    {
        DB::table('payment_schedules')
            ->where('loan_contract_id', $this->id)
            ->where('status', '!=', 'paid')
            ->delete();
    }
}