<?php

namespace App\Http\Controllers;

use App\Models\ContractTransaction;
use App\Models\LoanContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments');
    }

    public function data(Request $r)
    {
        LoanContract::markExpired();

        $today = today()->toDateString();
        $search = trim((string) $r->query('search'));
        $status = $r->query('status', 'open');
        $paidExpr = DB::raw('COALESCE(ps.paid_amount, ps.interest_amount)');

        $paidBase = fn() => DB::table('payment_schedules as ps')->where('ps.status', 'paid');
        $unpaid = fn() => DB::table('payment_schedules as ps')->where('ps.status', '!=', 'paid');
        $contractIn = fn() => ContractTransaction::whereIn('type', ['renew', 'extend', 'redeem']);
        $mStart = today()->startOfMonth()->toDateString();
        $mEnd = today()->endOfMonth()->toDateString();
        $in7 = today()->addDays(7)->toDateString();

        $stats = [
            'collected_today' => round($paidBase()->whereDate('ps.paid_date', $today)->sum($paidExpr)
                + $contractIn()->whereDate('date', $today)->sum('amount'), 2),
            'collected_month' => round($paidBase()->whereBetween('ps.paid_date', [$mStart, $mEnd])->sum($paidExpr)
                + $contractIn()->whereBetween('date', [$mStart, $mEnd])->sum('amount'), 2),
            'overdue_count' => $unpaid()->whereDate('ps.due_date', '<', $today)->count(),
            'overdue_amount' => round($unpaid()->whereDate('ps.due_date', '<', $today)->sum('ps.interest_amount'), 2),
            'soon_count' => $unpaid()->whereBetween('ps.due_date', [$today, $in7])->count(),
            'soon_amount' => round($unpaid()->whereBetween('ps.due_date', [$today, $in7])->sum('ps.interest_amount'), 2),
            'expired_contracts' => LoanContract::where('status', LoanContract::EXPIRED)->count(),
        ];

        /* ---------- ១ កិច្ចសន្យា = ១ ជួរ ---------- */
        $contracts = LoanContract::with('customer')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('contract_no', 'like', "%$search%")
                        ->orWhere('item_name', 'like', "%$search%")
                        ->orWhereHas('customer', fn($c) => $c->where('customer_name', 'like', "%$search%")
                            ->orWhere('phone_number', 'like', "%$search%"));
                });
            })
            ->when($status === 'open', fn($q) => $q->whereIn('status', LoanContract::OPEN))
            ->when($status === 'closed', fn($q) => $q->whereIn('status', [LoanContract::REDEEMED, LoanContract::AUCTIONED]))
            ->get();

        $rows = $contracts->map(function ($c) use ($today) {
            $sch = DB::table('payment_schedules')->where('loan_contract_id', $c->id)->orderBy('term_number')->get();
            $next = $sch->where('status', '!=', 'paid')->sortBy('due_date')->first();
            $nextDue = $next ? substr((string) $next->due_date, 0, 10) : null;

            return [
                'id' => $c->id,
                'contract_no' => $c->contract_no,
                'customer_id' => $c->customer?->id,
                'customer_name' => $c->customer?->customer_name,
                'phone_number' => $c->customer?->phone_number,
                'item_name' => $c->item_name,
                'contract_status' => $c->status,
                'paid_count' => $sch->where('status', 'paid')->count(),
                'total_count' => $sch->count(),
                'next_due' => $nextDue,
                'next_amount' => $next ? (float) $next->interest_amount : 0,
                'is_overdue' => $nextDue && $nextDue < $today,
                'days_overdue' => ($nextDue && $nextDue < $today) ? (int) Carbon::parse($nextDue)->diffInDays(today()) : 0,
            ];
        });

        if ($status === 'overdue') {
            $rows = $rows->where('is_overdue', true);
        }

        // ហួសកំណត់មុន រួចតាមថ្ងៃកំណត់ជិតបំផុត
        $rows = $rows->sortBy([['is_overdue', 'desc'], ['next_due', 'asc']])->values();

        $transactions = ContractTransaction::with('contract.customer')
            ->whereIn('type', ['renew', 'extend', 'redeem', 'auction'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('contract', function ($c) use ($search) {
                    $c->where('contract_no', 'like', "%$search%")
                        ->orWhere('item_name', 'like', "%$search%")
                        ->orWhereHas('customer', fn($x) => $x->where('customer_name', 'like', "%$search%"));
                });
            })
            ->latest('date')->latest('id')->limit(100)->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'date' => $t->date->format('Y-m-d'),
                'type' => $t->type,
                'amount' => (float) $t->amount,
                'note' => $t->note,
                'contract_no' => $t->contract?->contract_no,
                'item_name' => $t->contract?->item_name,
                'customer_id' => $t->contract?->customer?->id,
                'customer_name' => $t->contract?->customer?->customer_name,
            ]);

        return response()->json(['stats' => $stats, 'contracts' => $rows, 'transactions' => $transactions]);
    }

    /** កត់ត្រាការបង់ប្រាក់ */
    public function pay(Request $r, $id)
    {
        $d = $r->validate([
            'amount' => 'nullable|numeric|min:0',
            'method' => 'nullable|in:cash,aba,wing,bank,other',
            'note' => 'nullable|string|max:255',
        ]);

        $s = DB::table('payment_schedules')->where('id', $id)->first();
        abort_if(!$s, 404);

        if ($s->status === 'paid') {
            return response()->json(['message' => 'This installment is already paid.'], 422);
        }

        $update = [
            'status' => 'paid',
            'paid_date' => today()->toDateString(),
            'paid_amount' => $d['amount'] ?? $s->interest_amount,
            'payment_method' => $d['method'] ?? 'cash',
            'payment_note' => $d['note'] ?? null,
        ];
        if (Schema::hasColumn('payment_schedules', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('payment_schedules')->where('id', $id)->update($update);

        if ($r->expectsJson()) {
            return response()->json(['message' => 'Payment recorded.']);
        }
        return back()->with('success', 'បានកត់ត្រាការបង់ប្រាក់រួចរាល់!');
    }
}