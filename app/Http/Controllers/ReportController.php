<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\ContractTransaction;
use App\Models\Customer;
use App\Models\LoanContract;
use App\Models\PawnItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public const TYPES = [
        'customer' => 'Customer Report',
        'contract' => 'Pawn Contract Report',
        'loan' => 'Loan Report',
        'redemption' => 'Redemption Report',
        'auction' => 'Auction Report',
        'sales' => 'Sales Report',
        'outstanding' => 'Outstanding Loan Report',
        'revenue' => 'Revenue Report',
        'employee' => 'Employee Report',
        'inventory' => 'Inventory Report',
    ];

    public function index()
    {
        return view('admin.reports', ['types' => self::TYPES]);
    }

    public function data(Request $r)
    {
        LoanContract::markExpired();

        $type = $r->query('type', 'contract');
        abort_unless(isset(self::TYPES[$type]), 404);

        $from = $r->query('from', now()->startOfMonth()->toDateString());
        $to = $r->query('to', now()->endOfMonth()->toDateString());

        $out = $this->{'r_' . $type}($from, $to);
        $out['title'] = self::TYPES[$type];
        $out['from'] = $from;
        $out['to'] = $to;

        return response()->json($out);
    }

    private function d($v): string
    {
        return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—';
    }

    private function m($v): string
    {
        return '$' . number_format((float) $v, 2);
    }

    /* ---------------- Customer ---------------- */
    private function r_customer($from, $to): array
    {
        $rows = Customer::withCount('pawnItems')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->latest()->get()
            ->map(function ($c) {
                $contracts = LoanContract::where('customer_id', $c->id);
                return [
                    $c->id,
                    $c->customer_name,
                    $c->phone_number,
                    $c->passport_id,
                    $c->address_province,
                    ucfirst($c->customer_status ?? ''),
                    $c->pawn_items_count,
                    (clone $contracts)->count(),
                    $this->m((clone $contracts)->whereIn('status', LoanContract::OPEN)->sum('loan_amount')),
                    $this->d($c->created_at),
                ];
            })->all();

        return [
            'columns' => ['ID', 'Name', 'Phone', 'ID/Passport', 'Province', 'Status', 'Items', 'Contracts', 'Open loan', 'Registered'],
            'rows' => $rows,
            'summary' => [['New customers', count($rows)], ['Total customers', Customer::count()]],
        ];
    }

    /* ---------------- Pawn Contract ---------------- */
    private function r_contract($from, $to): array
    {
        $list = LoanContract::with('customer')->whereBetween('loan_date', [$from, $to])->orderBy('loan_date')->get();

        return [
            'columns' => ['Contract', 'Customer', 'Item', 'Loan', 'Rate', 'Loan date', 'Due date', 'Status'],
            'rows' => $list->map(fn($c) => [
                $c->contract_no,
                $c->customer?->customer_name,
                $c->item_name,
                $this->m($c->loan_amount),
                $c->interest_rate . '%',
                $this->d($c->loan_date),
                $this->d($c->due_date),
                ucfirst($c->status),
            ])->all(),
            'summary' => [['Contracts', $list->count()], ['Total principal', $this->m($list->sum('loan_amount'))]],
        ];
    }

    /* ---------------- Loan ---------------- */
    private function r_loan($from, $to): array
    {
        $list = LoanContract::with('customer')->whereBetween('loan_date', [$from, $to])->orderBy('loan_date')->get();
        $paid = fn($c) => (float) DB::table('payment_schedules')->where('loan_contract_id', $c->id)
            ->where('status', 'paid')->sum(DB::raw('COALESCE(paid_amount, interest_amount)'));

        $rows = $list->map(function ($c) use ($paid) {
            $p = $paid($c);
            return [
                $c->contract_no,
                $c->customer?->customer_name,
                $this->m($c->loan_amount),
                $c->interest_rate . '%',
                $c->term_months,
                $this->m(round($c->loan_amount * $c->interest_rate / 100, 2)),
                $this->m($p),
                ucfirst($c->status),
            ];
        })->all();

        return [
            'columns' => ['Contract', 'Customer', 'Principal', 'Rate', 'Term (m)', 'Monthly interest', 'Interest paid', 'Status'],
            'rows' => $rows,
            'summary' => [['Loans issued', $list->count()], ['Total lent', $this->m($list->sum('loan_amount'))]],
        ];
    }

    /* ---------------- Redemption ---------------- */
    private function r_redemption($from, $to): array
    {
        $tx = ContractTransaction::with('contract.customer')->where('type', 'redeem')
            ->whereBetween('date', [$from, $to])->orderBy('date')->get();

        return [
            'columns' => ['Date', 'Contract', 'Customer', 'Item', 'Principal', 'Total paid'],
            'rows' => $tx->map(fn($t) => [
                $this->d($t->date),
                $t->contract?->contract_no,
                $t->contract?->customer?->customer_name,
                $t->contract?->item_name,
                $this->m($t->contract?->loan_amount),
                $this->m($t->amount),
            ])->all(),
            'summary' => [['Redeemed', $tx->count()], ['Total collected', $this->m($tx->sum('amount'))]],
        ];
    }

    /* ---------------- Auction ---------------- */
    private function r_auction($from, $to): array
    {
        $list = Auction::with('contract.customer')->whereBetween('scheduled_date', [$from, $to])->orderBy('scheduled_date')->get();

        return [
            'columns' => ['Date', 'Contract', 'Customer', 'Item', 'Starting price', 'Status', 'Sale price'],
            'rows' => $list->map(fn($a) => [
                $this->d($a->scheduled_date),
                $a->contract?->contract_no,
                $a->contract?->customer?->customer_name,
                $a->contract?->item_name,
                $this->m($a->starting_price),
                ucfirst($a->status),
                $a->sale_price !== null ? $this->m($a->sale_price) : '—',
            ])->all(),
            'summary' => [
                ['Auctions', $list->count()],
                ['Scheduled', $list->where('status', 'scheduled')->count()],
                ['Sold', $list->where('status', 'sold')->count()],
                ['Unsold', $list->where('status', 'unsold')->count()],
            ],
        ];
    }

    /* ---------------- Sales ---------------- */
    private function r_sales($from, $to): array
    {
        $list = Auction::with('contract.customer')->where('status', 'sold')
            ->whereBetween('sold_date', [$from, $to])->orderBy('sold_date')->get();

        return [
            'columns' => ['Date', 'Contract', 'Item', 'Buyer', 'Owed', 'Sale price', 'Result'],
            'rows' => $list->map(fn($a) => [
                $this->d($a->sold_date),
                $a->contract?->contract_no,
                $a->contract?->item_name,
                $a->buyer_name,
                $this->m($a->owed_amount),
                $this->m($a->sale_price),
                ($a->sale_price - $a->owed_amount >= 0 ? '+' : '-') . $this->m(abs($a->sale_price - $a->owed_amount)),
            ])->all(),
            'summary' => [
                ['Items sold', $list->count()],
                ['Total sales', $this->m($list->sum('sale_price'))],
                ['Surplus (shop income)', $this->m($list->sum(fn($a) => max(0, $a->sale_price - $a->owed_amount)))],
            ],
        ];
    }

    /* ---------------- Outstanding (ថ្ងៃនេះ មិនប្រើតម្រងថ្ងៃ) ---------------- */
    private function r_outstanding($from, $to): array
    {
        $list = LoanContract::with('customer')->whereIn('status', LoanContract::OPEN)->orderBy('due_date')->get();

        return [
            'columns' => ['Contract', 'Customer', 'Item', 'Principal', 'Interest due', 'Total to redeem', 'Due date', 'Overdue (days)', 'Status'],
            'rows' => $list->map(fn($c) => [
                $c->contract_no,
                $c->customer?->customer_name,
                $c->item_name,
                $this->m($c->loan_amount),
                $this->m($c->accrued_interest),
                $this->m($c->total_due),
                $this->d($c->due_date),
                $c->days_overdue,
                ucfirst($c->status),
            ])->all(),
            'summary' => [
                ['Open contracts', $list->count()],
                ['Principal outstanding', $this->m($list->sum('loan_amount'))],
                ['Interest accrued', $this->m($list->sum('accrued_interest'))],
                ['Total to collect', $this->m($list->sum('total_due'))],
            ],
            'note' => 'Outstanding report shows the current position (date filter not applied).',
        ];
    }

    /* ---------------- Revenue ---------------- */
    private function r_revenue($from, $to): array
    {
        $rows = [];

        // ១. ការប្រាក់ប្រចាំខែបានបង់
        $interest = DB::table('payment_schedules as ps')
            ->leftJoin('loan_contracts as lc', 'lc.id', '=', 'ps.loan_contract_id')
            ->where('ps.status', 'paid')->whereBetween('ps.paid_date', [$from, $to])
            ->select('ps.paid_date', 'lc.contract_no', 'ps.term_number', DB::raw('COALESCE(ps.paid_amount, ps.interest_amount) as amt'))
            ->get();
        foreach ($interest as $i) {
            $rows[] = [$i->paid_date, $i->contract_no, 'Monthly interest', 'Month ' . $i->term_number, (float) $i->amt];
        }

        // ២. Renew (ការប្រាក់) + Extend (ថ្លៃសេវា)
        $tx = ContractTransaction::with('contract')->whereIn('type', ['renew', 'extend'])
            ->whereBetween('date', [$from, $to])->get();
        foreach ($tx as $t) {
            $rows[] = [
                $t->date->toDateString(),
                $t->contract?->contract_no,
                $t->type === 'renew' ? 'Renew interest' : 'Extension fee',
                $t->note,
                (float) $t->amount
            ];
        }

        // ៣. ការប្រាក់ពេល Redeem (ប្រាក់ដើមមិនរាប់)
        $red = ContractTransaction::with('contract')->where('type', 'redeem')->whereBetween('date', [$from, $to])->get();
        foreach ($red as $t) {
            $interestPart = max(0, (float) $t->amount - (float) ($t->contract?->loan_amount ?? 0));
            $rows[] = [$t->date->toDateString(), $t->contract?->contract_no, 'Redemption interest', 'Principal excluded', $interestPart];
        }

        // ៤. ចំណូលលើសពីការដេញថ្លៃ
        $sold = Auction::with('contract')->where('status', 'sold')->whereBetween('sold_date', [$from, $to])->get();
        foreach ($sold as $a) {
            $rows[] = [
                $a->sold_date->toDateString(),
                $a->contract?->contract_no,
                'Auction surplus',
                $a->buyer_name,
                max(0, (float) $a->sale_price - (float) $a->owed_amount)
            ];
        }

        usort($rows, fn($x, $y) => strcmp($x[0], $y[0]));
        $total = array_sum(array_column($rows, 4));

        $byType = collect($rows)->groupBy(2)->map(fn($g) => $g->sum(4));

        return [
            'columns' => ['Date', 'Contract', 'Source', 'Detail', 'Amount'],
            'rows' => array_map(fn($x) => [$this->d($x[0]), $x[1], $x[2], $x[3], $this->m($x[4])], $rows),
            'summary' => $byType->map(fn($v, $k) => [$k, $this->m($v)])->values()->push(['TOTAL REVENUE', $this->m($total)])->all(),
        ];
    }

    /* ---------------- Employee (ទុកពេលក្រោយ) ---------------- */
    private function r_employee($from, $to): array
    {
        $users = User::orderBy('name')->get();

        return [
            'columns' => ['ID', 'Name', 'Email', 'Created'],
            'rows' => $users->map(fn($u) => [$u->id, $u->name, $u->email, $this->d($u->created_at)])->all(),
            'summary' => [['Users', $users->count()]],
            'note' => 'Activity per employee will be available after adding user tracking and roles.',
        ];
    }

    /* ---------------- Inventory ---------------- */
    private function r_inventory($from, $to): array
    {
        $items = PawnItem::with('customer')->latest()->get();
        $label = ['active' => 'In Storage', 'redeemed' => 'Redeemed', 'auctioned' => 'Auctioned'];

        return [
            'columns' => ['ID', 'Item', 'Category', 'IMEI/Serial', 'Owner', 'Storage', 'Market value', 'Status'],
            'rows' => $items->map(fn($i) => [
                '#' . $i->id,
                $i->item_name,
                ucfirst($i->category),
                $i->imei_serial_tag,
                $i->customer?->customer_name,
                $i->storage_location,
                $this->m($i->estimated_market_value),
                $label[$i->item_status] ?? ucfirst($i->item_status),
            ])->all(),
            'summary' => [
                ['Total items', $items->count()],
                ['In storage', $items->where('item_status', 'active')->count()],
                ['Redeemed', $items->where('item_status', 'redeemed')->count()],
                ['Auctioned', $items->where('item_status', 'auctioned')->count()],
                ['Stock value (in storage)', $this->m($items->where('item_status', 'active')->sum('estimated_market_value'))],
            ],
            'note' => 'Inventory shows the current stock (date filter not applied).',
        ];
    }
}