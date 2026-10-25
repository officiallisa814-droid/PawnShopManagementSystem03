<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoanContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanContractController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.loanContract', [
            'customers' => Customer::orderBy('customer_name')->get(['id', 'customer_name', 'phone_number']),
            'pawnItem' => $request->filled('pawn_item')
                ? \App\Models\PawnItem::with('customer')->find($request->query('pawn_item'))
                : null,
            'defaults' => \App\Models\Setting::all_(),
        ]);
    }

    public function data(Request $request)
    {
        LoanContract::markExpired();

        $q = LoanContract::with(['customer', 'pawnItem'])->latest('id');

        if ($s = trim((string) $request->query('search'))) {
            $q->where(function ($w) use ($s) {
                $w->where('contract_no', 'like', "%$s%")
                    ->orWhere('item_name', 'like', "%$s%")
                    ->orWhereHas('customer', fn($c) => $c->where('customer_name', 'like', "%$s%")
                        ->orWhere('phone_number', 'like', "%$s%"));
            });
        }
        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }

        $all = LoanContract::all();
        $open = $all->where('is_open', true);

        return response()->json([
            'stats' => [
                'open_count' => $open->count(),
                'principal_out' => round($open->sum('loan_amount'), 2),
                'interest_accrued' => round($open->sum('accrued_interest'), 2),
                'expired_count' => $all->where('status', LoanContract::EXPIRED)->count(),
                'redeemed_count' => $all->where('status', LoanContract::REDEEMED)->count(),
                'auctioned_count' => $all->where('status', LoanContract::AUCTIONED)->count(),
                'due_7_days' => $open->filter(
                    fn($c) => $c->due_date >= today() && $c->due_date <= today()->addDays(7)
                )->count(),
            ],
            'contracts' => $q->get(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'pawn_item_id' => 'nullable|exists:pawn_items,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:30',
            'passport_id' => 'nullable|string|max:50',
            'item_name' => 'nullable|string|max:255',
            'item_description' => 'nullable|string',
            'item_value' => 'nullable|numeric|min:0',
            'loan_amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1|max:60',
            'loan_date' => 'required|date',
        ]);

        $pawnItem = !empty($d['pawn_item_id'])
            ? \App\Models\PawnItem::findOrFail($d['pawn_item_id'])
            : null;

        // ទំនិញមួយមានកិច្ចសន្យាបើកបានតែមួយ
        if ($pawnItem && $pawnItem->hasOpenContract()) {
            throw ValidationException::withMessages([
                'pawn_item_id' => 'This item already has an open contract.',
            ]);
        }

        // ទាញព័ត៌មានពីទំនិញ បើមាន
        if ($pawnItem) {
            $d['customer_id'] = $pawnItem->customer_id;
            $d['item_name'] = $pawnItem->item_name;
            $d['item_value'] = $d['item_value'] ?? $pawnItem->estimated_market_value;
        }

        if (empty($d['customer_id']) && empty($d['customer_name'])) {
            throw ValidationException::withMessages(['customer_name' => 'Customer is required.']);
        }
        if (empty($d['item_name'])) {
            throw ValidationException::withMessages(['item_name' => 'Item name is required.']);
        }
        $maxPct = (float) \App\Models\Setting::all_()['max_loan_percent'];
        $value = (float) ($d['item_value'] ?? 0);
        if ($value > 0 && $d['loan_amount'] > $value * $maxPct / 100) {
            throw ValidationException::withMessages([
                'loan_amount' => "Loan cannot exceed {$maxPct}% of item value (max "
                    . pawn_money($value * $maxPct / 100) . ').',
            ]);
        }

        $contract = DB::transaction(function () use ($d, $pawnItem) {
            $customer = !empty($d['customer_id'])
                ? Customer::findOrFail($d['customer_id'])
                : Customer::create([
                    'customer_name' => $d['customer_name'],
                    'phone_number' => $d['phone_number'] ?? null,
                    'passport_id' => $d['passport_id'] ?? null,
                ]);

            $loanDate = \Carbon\Carbon::parse($d['loan_date']);

            $c = LoanContract::create([
                'contract_no' => LoanContract::generateNumber(),
                'customer_id' => $customer->id,
                'pawn_item_id' => $pawnItem?->id,
                'item_name' => $d['item_name'],
                'item_description' => $d['item_description'] ?? null,
                'item_value' => $d['item_value'] ?? 0,
                'loan_amount' => $d['loan_amount'],
                'interest_rate' => $d['interest_rate'],
                'term_months' => $d['term_months'],
                'loan_date' => $loanDate,
                'due_date' => $loanDate->copy()->addMonthsNoOverflow((int) $d['term_months']),
                'interest_from' => $loanDate,
                'status' => LoanContract::ACTIVE,
            ]);

            $c->transactions()->create([
                'type' => 'create',
                'amount' => -$c->loan_amount,
                'date' => $loanDate,
                'note' => 'Loan paid out to customer',
            ]);
            $c->buildSchedule($loanDate);
            return $c;
        });

        return response()->json([
            'message' => 'Contract ' . $contract->contract_no . ' created.',
            'contract' => $contract->load('customer'),
        ], 201);
    }

    public function renew(Request $request, LoanContract $contract)
    {
        $this->assertOpen($contract);

        $d = $request->validate([
            'term_months' => 'nullable|integer|min:1|max:60',
        ]);

        DB::transaction(function () use ($contract, $d) {
            $interest = $contract->accrued_interest;          // ការប្រាក់ដែលអតិថិជនបង់ថ្ងៃនេះ
            $months = (int) ($d['term_months'] ?? $contract->term_months ?? 1);

            $contract->update([
                'interest_from' => today(),                       // ចាប់ផ្តើមរាប់ការប្រាក់ថ្មី
                'due_date' => today()->addMonthsNoOverflow($months),
                'status' => LoanContract::RENEWED,
            ]);
            $contract->clearUnpaidSchedules();
            $contract->buildSchedule(today(), $months);

            $contract->transactions()->create([
                'type' => 'renew',
                'amount' => $interest,
                'tax_amount' => pawn_tax($interest),
                'date' => today(),
                'note' => "Interest paid, renewed for {$months} month(s)",
            ]);
        });

        return response()->json(['message' => 'Contract renewed successfully.']);
    }

    public function extend(Request $request, LoanContract $contract)
    {
        $this->assertOpen($contract);

        $d = $request->validate([
            'months' => 'nullable|integer|min:1|max:60',
            'fee' => 'nullable|numeric|min:0',
        ]);

        $months = (int) ($d['months'] ?? 1);

        DB::transaction(function () use ($contract, $d, $months) {
            $base = $contract->due_date->lt(today()) ? today() : $contract->due_date;

            $contract->update([
                'due_date' => $base->copy()->addMonthsNoOverflow($months),
                'status' => LoanContract::RENEWED,
            ]);
            DB::table('payment_schedules')
                ->where('loan_contract_id', $contract->id)
                ->where('status', '!=', 'paid')
                ->get()
                ->each(fn($s) => DB::table('payment_schedules')->where('id', $s->id)->update([
                    'due_date' => \Carbon\Carbon::parse($s->due_date)->addMonthsNoOverflow($months)->toDateString(),
                ]));

            $contract->transactions()->create([
                'type' => 'extend',
                'amount' => $d['fee'] ?? 0,
                'date' => today(),
                'note' => "Due date extended by {$months} month(s)",
            ]);
        });

        return response()->json(['message' => 'Due date extended.']);
    }


    public function redeem(LoanContract $contract)
    {
        $this->assertOpen($contract);

        DB::transaction(function () use ($contract) {
            $total = $contract->total_due;

            $contract->update(['status' => LoanContract::REDEEMED, 'closed_at' => today()]);

            $contract->transactions()->create([
                'type' => 'redeem',
                'amount' => $total,
                'date' => today(),
                'note' => 'Principal + interest paid, item returned',
            ]);
            $contract->clearUnpaidSchedules();

            // ធ្វើបច្ចុប្បន្នភាពស្ថានភាពទំនិញ
            $contract->pawnItem?->update(['item_status' => 'redeemed']);
        });

        return response()->json(['message' => 'Contract closed. Item returned to customer.']);
    }

    public function auction(Request $request, LoanContract $contract)
    {
        if ($contract->status !== LoanContract::EXPIRED) {
            return response()->json(['message' => 'Only expired contracts can be auctioned.'], 422);
        }
        $d = $request->validate(['sale_amount' => 'nullable|numeric|min:0']);

        DB::transaction(function () use ($contract, $d) {
            $contract->update(['status' => LoanContract::AUCTIONED, 'closed_at' => today()]);

            $contract->transactions()->create([
                'type' => 'auction',
                'amount' => $d['sale_amount'] ?? 0,
                'tax_amount' => pawn_tax((float) ($d['sale_amount'] ?? 0)),
                'date' => today(),
                'note' => 'Item auctioned',
            ]);
            $contract->clearUnpaidSchedules();

            $contract->pawnItem?->update(['item_status' => 'auctioned']);
        });

        return response()->json(['message' => 'Item marked as auctioned.']);
    }

    public function print(LoanContract $contract)
    {
        return view('admin.loanContractPrint', ['c' => $contract->load('customer')]);
    }

    private function assertOpen(LoanContract $contract): void
    {
        LoanContract::markExpired();
        abort_unless($contract->fresh()->is_open, 422, 'This contract is already closed.');
    }

    // public function show(LoanContract $contract)
    // {
    //     LoanContract::markExpired();
    //     $contract = $contract->fresh(['customer', 'pawnItem', 'transactions']);

    //     $monthly = round($contract->loan_amount * $contract->interest_rate / 100, 2);
    //     $closed = !$contract->is_open;

    //     $schedule = [];
    //     for ($i = 1; $i <= $contract->term_months; $i++) {
    //         $due = $contract->interest_from->copy()->addMonthsNoOverflow($i);
    //         $schedule[] = [
    //             'term' => $i,
    //             'due' => $due,
    //             'amount' => $monthly,
    //             'state' => $closed ? 'closed' : ($due->isPast() ? 'passed' : 'upcoming'),
    //         ];
    //     }

    //     return view('admin.loanContractShow', compact('contract', 'monthly', 'schedule'));
    // }

    public function show(LoanContract $contract)
    {
        $contract->load(['customer', 'pawnItem', 'transactions']);
        // បើកិច្ចសន្យាបើក ហើយគ្មានកាលវិភាគ ឱ្យបង្កើតស្វ័យប្រវត្តិ
        if ($contract->is_open && !$contract->paymentSchedules()->exists()) {
            $contract->buildSchedule($contract->interest_from);
        }

        $monthly = round($contract->loan_amount * $contract->interest_rate / 100, 2);

        // កាលវិភាគពិតពី payment_schedules
        $schedule = \DB::table('payment_schedules')
            ->where('loan_contract_id', $contract->id)
            ->orderBy('term_number')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'term' => $s->term_number,
                'due' => \Carbon\Carbon::parse($s->due_date),
                'amount' => (float) $s->interest_amount,
                'status' => $s->status,
                'paid_date' => $s->paid_date,
            ]);

        return view('admin.loanContractShow', compact('contract', 'monthly', 'schedule'));
    }


}