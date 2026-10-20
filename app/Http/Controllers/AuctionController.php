<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\ContractTransaction;
use App\Models\LoanContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuctionController extends Controller
{
    public function index()
    {
        return view('admin.auction-sales');
    }

    public function data()
    {
        LoanContract::markExpired();

        $scheduledIds = Auction::where('status', 'scheduled')->pluck('loan_contract_id');

        $expired = LoanContract::with('customer')
            ->where('status', LoanContract::EXPIRED)
            ->whereNotIn('id', $scheduledIds)
            ->orderBy('due_date')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'contract_no' => $c->contract_no,
                'customer_name' => $c->customer?->customer_name,
                'phone_number' => $c->customer?->phone_number,
                'item_name' => $c->item_name,
                'loan_amount' => $c->loan_amount,
                'owed' => $c->total_due,
                'due_date' => $c->due_date->format('Y-m-d'),
                'days_overdue' => $c->days_overdue,
            ]);

        $map = fn($a) => [
            'id' => $a->id,
            'contract_no' => $a->contract?->contract_no,
            'customer_name' => $a->contract?->customer?->customer_name,
            'item_name' => $a->contract?->item_name,
            'owed' => $a->owed_amount ?? $a->contract?->total_due,
            'scheduled_date' => $a->scheduled_date->format('Y-m-d'),
            'starting_price' => $a->starting_price,
            'status' => $a->status,
            'buyer_name' => $a->buyer_name,
            'buyer_phone' => $a->buyer_phone,
            'sale_price' => $a->sale_price,
            'sold_date' => $a->sold_date?->format('Y-m-d'),
            'result' => $a->sale_price !== null ? round($a->sale_price - ($a->owed_amount ?? 0), 2) : null,
            'note' => $a->note,
        ];

        $scheduled = Auction::with('contract.customer')->where('status', 'scheduled')
            ->orderBy('scheduled_date')->get()->map($map);

        $history = Auction::with('contract.customer')->whereIn('status', ['sold', 'unsold', 'cancelled'])
            ->latest('updated_at')->limit(200)->get()->map($map);

        $sold = Auction::where('status', 'sold')->get();

        $stats = [
            'expired_count' => $expired->count(),
            'scheduled_count' => $scheduled->count(),
            'sold_count' => $sold->count(),
            'revenue' => round($sold->sum('sale_price'), 2),
            'surplus' => round($sold->sum(fn($a) => max(0, $a->sale_price - $a->owed_amount)), 2),
        ];

        return response()->json(compact('stats', 'expired', 'scheduled', 'history'));
    }

    public function schedule(Request $r)
    {
        $d = $r->validate([
            'loan_contract_id' => 'required|exists:loan_contracts,id',
            'scheduled_date' => 'required|date',
            'starting_price' => 'required|numeric|min:0',
        ]);

        $contract = LoanContract::findOrFail($d['loan_contract_id']);

        if ($contract->status !== LoanContract::EXPIRED) {
            throw ValidationException::withMessages(['loan_contract_id' => 'Only expired contracts can be auctioned.']);
        }
        if (Auction::where('loan_contract_id', $contract->id)->where('status', 'scheduled')->exists()) {
            throw ValidationException::withMessages(['loan_contract_id' => 'This contract already has a scheduled auction.']);
        }

        Auction::create($d + ['pawn_item_id' => $contract->pawn_item_id, 'status' => 'scheduled']);

        return response()->json(['message' => 'បានកំណត់ការដេញថ្លៃរួចរាល់ / Auction scheduled.']);
    }

    public function result(Request $r, Auction $auction)
    {
        abort_unless($auction->status === 'scheduled', 422, 'This auction is already closed.');

        $d = $r->validate([
            'outcome' => 'required|in:sold,unsold',
            'buyer_name' => 'required_if:outcome,sold|nullable|string|max:255',
            'buyer_phone' => 'nullable|string|max:30',
            'sale_price' => 'required_if:outcome,sold|nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($auction, $d) {
            $contract = $auction->contract;

            if ($d['outcome'] === 'unsold') {
                $auction->update(['status' => 'unsold', 'note' => $d['note'] ?? null]);
                return; // កិច្ចសន្យានៅ expired អាចកំណត់ថ្ងៃម្តងទៀត
            }

            $owed = $contract->total_due;

            $auction->update([
                'status' => 'sold',
                'buyer_name' => $d['buyer_name'],
                'buyer_phone' => $d['buyer_phone'] ?? null,
                'sale_price' => $d['sale_price'],
                'owed_amount' => $owed,
                'sold_date' => today(),
                'note' => $d['note'] ?? null,
            ]);

            $contract->update(['status' => LoanContract::AUCTIONED, 'closed_at' => today()]);
            $contract->clearUnpaidSchedules();
            $contract->pawnItem?->update(['item_status' => 'auctioned']);

            ContractTransaction::create([
                'loan_contract_id' => $contract->id,
                'type' => 'auction',
                'amount' => $d['sale_price'],
                'date' => today(),
                'note' => 'Auctioned to ' . $d['buyer_name'],
            ]);
        });

        return response()->json(['message' => 'បានកត់ត្រាលទ្ធផលរួចរាល់ / Result recorded.']);
    }

    public function cancel(Auction $auction)
    {
        abort_unless($auction->status === 'scheduled', 422, 'This auction is already closed.');
        $auction->update(['status' => 'cancelled']);

        return response()->json(['message' => 'បានលុបចោលការដេញថ្លៃ / Auction cancelled.']);
    }
}