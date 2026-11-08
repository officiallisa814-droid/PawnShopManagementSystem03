<?php

namespace App\Http\Controllers;

use App\Models\LoanContract;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /** ការជូនដំណឹងបង្កើតផ្ទាល់ពីទិន្នន័យ តាមសិទ្ធិរបស់ Role */
    public function index(Request $request)
    {
        $user = $request->user();
        $sym = Setting::all_()['currency_symbol'] ?? '$';
        $items = [];

        /* ---------- កិច្ចសន្យា ---------- */
        if ($user->can('contracts.view')) {
            LoanContract::markExpired();

            // ១. ហួសកំណត់
            $expired = LoanContract::with('customer:id,customer_name')
                ->where('status', LoanContract::EXPIRED)
                ->orderBy('due_date')->limit(5)->get();

            foreach ($expired as $c) {
                $items[] = [
                    'key' => 'exp-' . $c->id,
                    'level' => 'danger',
                    'icon' => 'fa-triangle-exclamation',
                    'title' => 'កិច្ចសន្យាហួសកំណត់ ' . $c->contract_no,
                    'text' => ($c->customer->customer_name ?? '—') . ' · ' . $c->item_name,
                    'meta' => 'ហួស ' . $c->days_overdue . ' ថ្ងៃ · ' . $sym . number_format($c->loan_amount, 2),
                    'url' => route('loanContract.show', $c->id),
                ];
            }

            // ២. ជិតដល់ថ្ងៃកំណត់ (៧ ថ្ងៃ)
            $soon = LoanContract::with('customer:id,customer_name')
                ->whereIn('status', [LoanContract::ACTIVE, LoanContract::RENEWED])
                ->whereBetween('due_date', [today(), today()->addDays(7)])
                ->orderBy('due_date')->limit(5)->get();

            foreach ($soon as $c) {
                $left = (int) today()->diffInDays($c->due_date, false);
                $items[] = [
                    'key' => 'soon-' . $c->id . '-' . $c->due_date->toDateString(),
                    'level' => 'warn',
                    'icon' => 'fa-clock',
                    'title' => 'ជិតដល់ថ្ងៃកំណត់ ' . $c->contract_no,
                    'text' => ($c->customer->customer_name ?? '—') . ' · ' . $c->item_name,
                    'meta' => ($left <= 0 ? 'ថ្ងៃនេះ' : 'នៅសល់ ' . $left . ' ថ្ងៃ') . ' · ' . $c->due_date->format('d/m/Y'),
                    'url' => route('loanContract.show', $c->id),
                ];
            }
        }

        /* ---------- ការប្រាក់ហួសកំណត់ ---------- */
        if ($user->can('payments.view')) {
            $rows = DB::table('payment_schedules as ps')
                ->join('loan_contracts as lc', 'lc.id', '=', 'ps.loan_contract_id')
                ->leftJoin('customers as c', 'c.id', '=', 'lc.customer_id')
                ->where('ps.status', '!=', 'paid')
                ->whereDate('ps.due_date', '<', today())
                ->whereIn('lc.status', [LoanContract::ACTIVE, LoanContract::RENEWED, LoanContract::EXPIRED])
                ->orderBy('ps.due_date')
                ->limit(5)
                ->get(['ps.id', 'ps.loan_contract_id', 'ps.term_number', 'ps.due_date', 'ps.interest_amount', 'lc.contract_no', 'c.customer_name']);

            foreach ($rows as $r) {
                $days = (int) Carbon::parse($r->due_date)->diffInDays(today());
                $items[] = [
                    'key' => 'ps-' . $r->id,
                    'level' => 'danger',
                    'icon' => 'fa-money-bill-wave',
                    'title' => 'ការប្រាក់ខែទី ' . $r->term_number . ' មិនទាន់បង់',
                    'text' => ($r->customer_name ?? '—') . ' · ' . $r->contract_no,
                    'meta' => 'ហួស ' . $days . ' ថ្ងៃ · ' . $sym . number_format($r->interest_amount, 2),
                    'url' => route('loanContract.show', $r->loan_contract_id),
                ];
            }
        }

        if ($user->can('users.manage')) {
            $pending = User::where('role', 'pending')->latest()->limit(5)->get();

            foreach ($pending as $p) {
                $items[] = [
                    'key' => 'pend-' . $p->id,
                    'level' => 'info',
                    'icon' => 'fa-user-clock',
                    'title' => 'សំណើចុះឈ្មោះថ្មី',
                    'text' => $p->name . ' · ' . $p->email,
                    'meta' => 'រង់ចាំអនុម័ត · ' . optional($p->created_at)->format('d/m/Y H:i'),
                    'url' => route('users.index'),
                ];
            }
        }

        return response()->json(['items' => $items]);
    }
}