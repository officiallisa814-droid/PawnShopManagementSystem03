<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoanContract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /** រក្សាទុកអតិថិជនថ្មី (ពីម៉ូដាល់ក្នុងទំព័រ /customer) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
            'dob' => 'required|date|before:today',
            'passport_id' => 'required|string|max:50',
            'phone_number' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'address_house' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_village' => 'required|string|max:255',
            'address_district' => 'required|string|max:255',
            'address_province' => 'required|string|max:255',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:30',
            'active_pledges' => 'nullable|integer|min:0',
            'customer_type' => 'nullable|in:regular,vip,bad',
            'customer_status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
            'id_card_photo' => 'nullable|image|max:4096',
            'customer_avatar' => 'nullable|image|max:4096',
        ], [
            'customer_name' => 'ឈ្មោះអតិថិជន',
            'gender' => 'ភេទ',
            'dob' => 'ថ្ងៃខែឆ្នាំកំណើត',
            'passport_id' => 'អត្តសញ្ញាណប័ណ្ណ ឬ លិខិតឆ្លងដែន',
            'phone_number' => 'លេខទូរស័ព្ទ',
            'email' => 'អ៊ីមែល',
            'address_house' => 'ផ្ទះលេខ',
            'address_street' => 'ផ្លូវ',
            'address_village' => 'ភូមិ-ឃុំ-សង្កាត់',
            'address_district' => 'ក្រុង-ស្រុក-ខណ្ឌ',
            'address_province' => 'ខេត្ត-រាជធានី',
            'guarantor_name' => 'ឈ្មោះអ្នកធានា',
            'guarantor_phone' => 'លេខទូរស័ព្ទអ្នកធានា',
            'active_pledges' => 'ចំនួនបញ្ចាំសកម្ម',
            'customer_type' => 'ចំណាត់ថ្នាក់អតិថិជន',
            'customer_status' => 'ស្ថានភាពគណនី',
            'id_card_photo' => 'រូបថតអត្តសញ្ញាណប័ណ្ណ',
            'customer_avatar' => 'រូបថតផ្ទាល់មុខអតិថិជន',
            'notes' => 'កំណត់ចំណាំ',
        ]);

        foreach (['id_card_photo', 'customer_avatar'] as $file) {
            if ($request->hasFile($file)) {
                $data[$file] = $request->file($file)->store('customers', 'public');
            }
        }

        $data['active_pledges'] = $data['active_pledges'] ?? 0;
        $data['customer_type'] = $data['customer_type'] ?? 'regular';
        $data['customer_status'] = $data['customer_status'] ?? 'active';

        Customer::create($data);

        return redirect()->route('customer')->with('success', 'បានបន្ថែមអតិថិជនថ្មីរួចរាល់!');
    }

    /** ស្វែងរកអតិថិជនសម្រាប់ Autocomplete */
    public function searchApi(Request $request)
    {
        $q = trim((string) $request->query('query'));

        if ($q === '') {
            return response()->json([]);
        }

        $like = '%' . addcslashes($q, '%_\\') . '%';

        $customers = Customer::query()
            ->where(function ($w) use ($like, $q) {
                $w->where('customer_name', 'like', $like)
                    ->orWhere('phone_number', 'like', $like)
                    ->orWhere('passport_id', 'like', $like);

                if (ctype_digit($q)) {
                    $w->orWhere('id', (int) $q);
                }
            })
            ->orderBy('customer_name')
            ->limit(10)
            ->get(['id', 'customer_name', 'phone_number']);

        return response()->json($customers);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
            'dob' => 'required|date|before:today',
            'passport_id' => 'required|string|max:50',
            'phone_number' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'address_house' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_village' => 'required|string|max:255',
            'address_district' => 'required|string|max:255',
            'address_province' => 'required|string|max:255',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:30',
            'customer_type' => 'nullable|in:regular,vip,bad',
            'customer_status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
            'id_card_photo' => 'nullable|image|max:4096',
            'customer_avatar' => 'nullable|image|max:4096',
        ]);

        foreach (['id_card_photo', 'customer_avatar'] as $file) {
            if ($request->hasFile($file)) {
                if ($customer->$file) {
                    Storage::disk('public')->delete($customer->$file);
                }
                $data[$file] = $request->file($file)->store('customers', 'public');
            }
        }

        $customer->update($data);

        return redirect()->back()->with('success', 'បានកែប្រែព័ត៌មានអតិថិជនរួចរាល់!');
    }

    public function destroy(Customer $customer)
    {
        // មិនឱ្យលុបបើមានទំនិញ ឬកិច្ចសន្យា
        if ($customer->pawnItems()->exists() || $customer->loanContracts()->exists()) {
            return redirect()->route('customer')
                ->with('error', 'មិនអាចលុបបានទេ ព្រោះអតិថិជននេះមានទំនិញ ឬកិច្ចសន្យា។ សូមប្តូរស្ថានភាពទៅ Inactive ជំនួស។');
        }

        foreach (['id_card_photo', 'customer_avatar'] as $file) {
            if ($customer->$file) {
                Storage::disk('public')->delete($customer->$file);
            }
        }

        $customer->delete();

        return redirect()->route('customer')->with('success', 'បានលុបអតិថិជនរួចរាល់!');
    }

    public function show(Customer $customer)
    {
        LoanContract::markExpired();

        $customer->load([
            'pawnItems' => fn($q) => $q->latest('id'),
            'loanContracts' => fn($q) => $q->latest('id'),
        ]);

        return view('admin.customerShow', compact('customer')); // ប្តូរឈ្មោះ view តាម file ពិតរបស់អ្នក
    }
}