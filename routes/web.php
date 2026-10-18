<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PawnItemController;
use App\Http\Controllers\LoanContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Customer;
use App\Models\PawnItem;
use App\Models\PaymentSchedule;

// ==========================================================
// 🔓 PUBLIC ROUTES
// ==========================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/signup', function () {
    return view('auth.signup');
})->name('signup');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================================
// 🔒 PROTECTED STAFF ROUTES
// ==========================================================
Route::middleware(['auth'])->group(function () {

    // ----- Dashboard -----
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----- Customers -----
    Route::get('/customer', function () {
        $customers = Customer::with('pawnItems')->latest()->get();
        return view('admin.customer', compact('customers'));
    })->name('customer');

    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/api/search-customers', [CustomerController::class, 'searchApi'])->name('customers.searchApi');

    Route::get('/customer/{id}', function ($id) {
        $customer = Customer::findOrFail($id);
        return view('admin.customer-show', compact('customer'));
    })->name('customer.show');

    // ----- Pawn Items -----
    Route::get('/pawnItem', [PawnItemController::class, 'index'])->name('pawnItem');
    Route::get('/pawnItem/{id}', [PawnItemController::class, 'show'])->whereNumber('id')->name('pawnItem.show');
    Route::post('/pawn-items', [PawnItemController::class, 'store'])->name('pawnItems.store');
    Route::put('/pawn-items/{item}', [PawnItemController::class, 'update'])->name('pawnItems.update');
    Route::delete('/pawn-item-photos/{photo}', [PawnItemController::class, 'destroyPhoto'])->name('pawnItems.photo.destroy');

    // ទំនិញរបស់អតិថិជន (ប្រើក្នុង modal Loan Contract)
    Route::get('/api/customers/{customer}/pawn-items', [PawnItemController::class, 'byCustomer'])
        ->name('customers.pawnItems');

    // រកម្ចាស់ទំនិញ (ពីប៊ូតុង Contract)
    Route::get('/api/pawn-item-owner/{item}', function ($item) {
        return response()->json(['customer_id' => PawnItem::findOrFail($item)->customer_id]);
    });

    Route::post('/payment-schedule/{id}/pay', function ($id) {
        $schedule = PaymentSchedule::findOrFail($id);
        $schedule->update([
            'status' => 'paid',
            'paid_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->back()->with('success', 'បានកត់ត្រាការបង់ប្រាក់រួចរាល់!');
    })->name('paymentSchedule.pay');


    // ----- Loan Contract -----
    Route::get('/loanContract', [LoanContractController::class, 'index'])->name('loanContract');

    Route::get('/loanContract/{contract}', [LoanContractController::class, 'show'])
        ->whereNumber('contract')
        ->name('loanContract.show');

    Route::prefix('loan-contracts')->name('loan-contracts.')->group(function () {
        Route::get('/data', [LoanContractController::class, 'data'])->name('data');
        Route::post('/', [LoanContractController::class, 'store'])->name('store');
        Route::post('/{contract}/renew', [LoanContractController::class, 'renew'])->name('renew');
        Route::post('/{contract}/extend', [LoanContractController::class, 'extend'])->name('extend');
        Route::post('/{contract}/redeem', [LoanContractController::class, 'redeem'])->name('redeem');
        Route::post('/{contract}/auction', [LoanContractController::class, 'auction'])->name('auction');
        Route::get('/{contract}/print', [LoanContractController::class, 'print'])->name('print');
    });

    // ----- Payments -----
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::get('/payments/data', [PaymentController::class, 'data'])->name('payments.data');
    Route::post('/payments/{id}/pay', [PaymentController::class, 'pay'])->name('payments.pay');

    // ----- Other menus -----
    Route::get('/auction-sales', function () {
        return view('admin.auction-sales');
    })->name('auction-sales');

    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('reports');

    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

    // ----- Customer portal -----
    Route::get('/my-pledges', function () {
        return "Welcome to your personal client tracking portal layout view page!";
    })->name('customer.pledges');
});