<?php

namespace App\Http\Controllers;

use App\Models\PawnItem;
use App\Models\Customer;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use App\Models\LoanContract;
use Illuminate\Support\Facades\Storage;

class PawnItemController extends Controller
{
    /**
     * Show form page and pawned items inventory table.
     */
    public function index()
    {
        // 1. Get all customers for live searching dropdown list
        $customers = Customer::latest()->get();

        // 2. Get all pawn items with customer relationship data loaded
        $pawnItems = PawnItem::with('customer')->latest()->get();

        return view('admin.pawnItem', compact('customers', 'pawnItems'));
    }

    public function byCustomer($customerId)
    {
        $items = PawnItem::where('customer_id', $customerId)->latest()->get();
        $ids = $items->pluck('id');

        $busyContractIds = LoanContract::whereIn('pawn_item_id', $ids)
            ->whereIn('status', LoanContract::OPEN)
            ->pluck('pawn_item_id')
            ->all();

        $newestId = $items->first()?->id;

        return response()->json(
            $items->map(function ($it) use ($busyContractIds, $newestId) {
                $busy = in_array($it->id, $busyContractIds);

                return [
                    'id' => $it->id,
                    'item_name' => $it->item_name,
                    'category' => $it->category,
                    'serial' => $it->imei_serial_tag,
                    'market_value' => (float) $it->estimated_market_value,
                    'approved_loan' => (float) $it->approved_loan,
                    'created_at' => $it->created_at?->format('d/m/Y H:i'),
                    'is_newest' => $it->id === $newestId,
                    'available' => !$busy,
                    'reason' => $busy ? 'កំពុងមានកិច្ចសន្យាបញ្ចាំរួចហើយ' : null,
                ];
            })->values()
        );
    }

    /**
     * Save form inputs data directly into database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:pawn_items,imei_serial_tag',
            'storage_location' => 'required|string|max:255',
            'accessories' => 'nullable|string|max:255',
            'market_value' => 'required|numeric|min:0',
            'item_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'condition_details' => 'nullable|string',
        ], [
            'serial_number.unique' => 'លេខសៀរៀល/IMEI នេះមានក្នុងប្រព័ន្ធរួចហើយ / This IMEI/Serial already exists.',
        ]);

        if ($request->hasFile('item_photo')) {
            $validatedData['item_photo'] = $request->file('item_photo')->store('pawn_items', 'public');
        }

        $pawnItem = new PawnItem();
        $pawnItem->customer_id = $validatedData['customer_id'];
        $pawnItem->category = $validatedData['category'];
        $pawnItem->item_name = $validatedData['item_name'];
        $pawnItem->imei_serial_tag = $validatedData['serial_number'];
        $pawnItem->storage_location = $validatedData['storage_location'];
        $pawnItem->included_accessories = $validatedData['accessories'] ?? null;
        $pawnItem->estimated_market_value = $validatedData['market_value'];
        $pawnItem->item_photo = $validatedData['item_photo'] ?? null;
        $pawnItem->item_condition_details = $validatedData['condition_details'] ?? null;
        $pawnItem->item_status = 'active';
        $pawnItem->save();

        foreach ($request->file('photos', []) as $file) {
            $pawnItem->photos()->create(['path' => $file->store('pawn_items', 'public')]);
        }

        return redirect()->back()->with('success', 'Pawn item added successfully!');

    }

    public function show($id)
    {
        $item = PawnItem::with(['customer', 'photos', 'loanContracts'])->findOrFail($id);
        $maxLoan = round(($item->estimated_market_value ?? 0) * config('pawn.max_loan_percent', 70) / 100, 2);
        $hasOpen = $item->hasOpenContract();

        return view('admin.pawnItem-show', compact('item', 'maxLoan', 'hasOpen'));
    }

    public function update(Request $request, PawnItem $item)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:pawn_items,imei_serial_tag,' . $item->id,
            'storage_location' => 'required|string|max:255',
            'accessories' => 'nullable|string|max:255',
            'market_value' => 'required|numeric|min:0',
            'condition_details' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $item->update([
            'category' => $data['category'],
            'item_name' => $data['item_name'],
            'imei_serial_tag' => $data['serial_number'],
            'storage_location' => $data['storage_location'],
            'included_accessories' => $data['accessories'] ?? null,
            'estimated_market_value' => $data['market_value'],
            'item_condition_details' => $data['condition_details'] ?? null,
        ]);

        foreach ($request->file('photos', []) as $file) {
            $item->photos()->create(['path' => $file->store('pawn_items', 'public')]);
        }

        return redirect()->route('pawnItem.show', $item->id)->with('success', 'បានកែប្រែទំនិញរួចរាល់!');
    }

    public function destroyPhoto(\App\Models\PawnItemPhoto $photo)
    {
        Storage::disk('public')->delete($photo->path);
        $id = $photo->pawn_item_id;
        $photo->delete();

        return redirect()->route('pawnItem.show', $id)->with('success', 'បានលុបរូបរួចរាល់!');
    }
}
