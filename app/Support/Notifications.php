<?php

namespace App\Support;

use App\Models\LoanContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Notifications
{
    public static function all(): array
    {
        $u = auth()->user();
        if (!$u)
            return [];

        $today = today()->toDateString();
        $in3 = today()->addDays(3)->toDateString();
        $items = [];
        $unpaid = fn() => DB::table('payment_schedules')->where('status', '!=', 'paid');

        // Payments (admin, manager, cashier)
        if ($u->hasRole('admin', 'manager', 'cashier')) {
            if ($n = $unpaid()->whereDate('due_date', '<', $today)->count())
                $items[] = [
                    'icon' => 'fa-triangle-exclamation',
                    'color' => 'red',
                    'text' => "ហួសកំណត់បង់ការប្រាក់ {$n} ខែ",
                    'url' => route('payments')
                ];

            if ($n = $unpaid()->whereBetween('due_date', [$today, $in3])->count())
                $items[] = [
                    'icon' => 'fa-clock',
                    'color' => 'amber',
                    'text' => "ជិតដល់ថ្ងៃបង់ (៣ ថ្ងៃ) {$n} ខែ",
                    'url' => route('payments')
                ];
        }

        // Auction (admin, manager)
        if ($u->hasRole('admin', 'manager')) {
            if ($n = LoanContract::where('status', LoanContract::EXPIRED)->count())
                $items[] = [
                    'icon' => 'fa-file-circle-xmark',
                    'color' => 'red',
                    'text' => "កិច្ចសន្យាហួសកំណត់ {$n}",
                    'url' => route('auction-sales')
                ];

            // ដេញថ្លៃថ្ងៃនេះ (ត្រូវមាន column auction_date ក្នុង loan_contracts)
            if (
                \Schema::hasColumn('loan_contracts', 'auction_date')
                && ($n = LoanContract::whereDate('auction_date', $today)->where('status', LoanContract::EXPIRED)->count())
            )
                $items[] = [
                    'icon' => 'fa-gavel',
                    'color' => 'blue',
                    'text' => "ដេញថ្លៃថ្ងៃនេះ {$n}",
                    'url' => route('auction-sales')
                ];
        }

        // គណនីរង់ចាំអនុម័ត (admin)
        if ($u->isAdmin() && ($n = User::where('role', 'staff')->count()))
            $items[] = [
                'icon' => 'fa-user-clock',
                'color' => 'blue',
                'text' => "គណនីរង់ចាំអនុម័ត {$n}",
                'url' => route('users.index')
            ];

        return $items;
    }
}