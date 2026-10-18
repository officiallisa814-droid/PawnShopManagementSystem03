<?php

namespace App\Http\Controllers;

use App\Models\ContractTransaction;
use App\Models\Customer;
use App\Models\LoanContract;
use App\Models\PawnItem;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        LoanContract::markExpired();

        $open = LoanContract::whereIn('status', LoanContract::OPEN);

        $totalCustomers  = Customer::count();
        $activePawnItems = PawnItem::where('item_status', 'active')->count();
        $totalLoanAmount = (clone $open)->sum('loan_amount');
        $recentPawnItems = PawnItem::with('customer')->latest()->take(5)->get();

        /* ---------- Loan Overview (ខែនេះ ថ្ងៃទៅថ្ងៃ) ---------- */
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();
        $days  = $start->daysInMonth;

        $tx = ContractTransaction::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        $labels = $issued = $collected = [];
        for ($d = 1; $d <= $days; $d++) {
            $date = Carbon::create($start->year, $start->month, $d)->toDateString();
            $dayTx = $tx->filter(fn ($t) => $t->date->toDateString() === $date);

            $labels[]    = $d;
            // type 'create' amount ជាអវិជ្ជមាន (ប្រាក់ចេញ)
            $issued[]    = round(abs($dayTx->where('type', 'create')->sum('amount')), 2);
            $collected[] = round($dayTx->whereIn('type', ['redeem', 'renew', 'extend'])->sum('amount'), 2);
        }

        $loanChart = ['labels' => $labels, 'issued' => $issued, 'collected' => $collected];

        /* ---------- Pawn Items by Category ---------- */
        $names = [
            'electronics' => 'Electronics',
            'jewelry'     => 'Jewelry',
            'vehicle'     => 'Vehicle',
        ];

        $counts = PawnItem::selectRaw('LOWER(category) as cat, COUNT(*) as total')
            ->groupBy('cat')
            ->pluck('total', 'cat');

        $grand = (int) $counts->sum();

        $categories = $counts->map(fn ($total, $cat) => [
            'name'    => $names[$cat] ?? ucfirst($cat ?: 'Other'),
            'count'   => (int) $total,
            'percent' => $grand ? round($total / $grand * 100, 1) : 0,
        ])->values();

                /* ---------- Loan Status Summary ---------- */
        LoanContract::markExpired();

        $statusCounts = LoanContract::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusTotal = (int) $statusCounts->sum();

        $rows = [
            ['label' => 'Active',    'count' => (int) ($statusCounts['active'] ?? 0) + (int) ($statusCounts['renewed'] ?? 0), 'bar' => 'active-bar'],
            ['label' => 'Redeemed',  'count' => (int) ($statusCounts['redeemed'] ?? 0),  'bar' => 'redeemed-bar'],
            ['label' => 'Overdue',   'count' => (int) ($statusCounts['expired'] ?? 0),   'bar' => 'overdue-bar'],
            ['label' => 'Auctioned', 'count' => (int) ($statusCounts['auctioned'] ?? 0), 'bar' => 'cancelled-bar'],
        ];

        $loanStatus = array_map(function ($r) use ($statusTotal) {
            $r['percent'] = $statusTotal ? round($r['count'] / $statusTotal * 100, 1) : 0;
            return $r;
        }, $rows);

        return view('admin.dashboard', compact(
            'totalCustomers', 'activePawnItems', 'totalLoanAmount', 'recentPawnItems',
            'loanChart', 'categories', 'grand', 'loanStatus'
        ));
    }
}