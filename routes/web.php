<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PawnItemController;
use App\Http\Controllers\LoanContractController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\Customer;
use App\Models\PawnItem;

// ==========================================================
// 🔓 PUBLIC ROUTES
// ==========================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Signup: គណនីថ្មីស្ថិតក្នុងស្ថានភាព "pending" រហូតដល់ Admin អនុម័ត និងកំណត់ Role
Route::get('/signup', [RegisterController::class, 'show'])->name('signup');
Route::post('/signup', [RegisterController::class, 'store'])->middleware('throttle:5,10')->name('signup.store');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================================
// 🔒 PROTECTED ROUTES  (ត្រូវ Login + គណនីសកម្ម)
// 'can:xxx' = ត្រួតពិនិត្យសិទ្ធិតាម config/roles.php
// ==========================================================
Route::middleware(['auth', 'active'])->group(function () {

    // ----- Profile (គ្រប់ Role) -----
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

    // ----- Dashboard -----
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.view')->name('dashboard');

    // ----- Customers -----
    // blacklist ត្រូវនៅមុន /customer/{id}
    Route::get('/customer/blacklist', [CustomerController::class, 'blacklist'])
        ->middleware('can:customers.delete')->name('customer.blacklist');

    Route::middleware('can:customers.view')->group(function () {
        Route::get('/customer', function () {
            $customers = Customer::with('pawnItems')->latest()->get();
            return view('admin.customer', compact('customers'));
        })->name('customer');

        Route::get('/customer/{id}', function ($id) {
            $customer = Customer::findOrFail($id);
            return view('admin.customer-show', compact('customer'));
        })->whereNumber('id')->name('customer.show');

        Route::get('/api/search-customers', [CustomerController::class, 'searchApi'])->name('customers.searchApi');
    });

    Route::middleware('can:customers.manage')->group(function () {
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    });

    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('can:customers.delete')->name('customers.destroy');

    // ----- Reports -----
    Route::middleware('can:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');
    });

    // ----- Pawn Items -----
    Route::middleware('can:pawnitems.view')->group(function () {
        Route::get('/pawnItem', [PawnItemController::class, 'index'])->name('pawnItem');
        Route::get('/pawnItem/{id}', [PawnItemController::class, 'show'])->whereNumber('id')->name('pawnItem.show');
    });

    Route::middleware('can:pawnitems.manage')->group(function () {
        Route::post('/pawn-items', [PawnItemController::class, 'store'])->name('pawnItems.store');
        Route::put('/pawn-items/{item}', [PawnItemController::class, 'update'])->name('pawnItems.update');
        Route::delete('/pawn-items/{item}/main-photo', [PawnItemController::class, 'destroyMainPhoto'])->name('pawnItems.mainPhoto.destroy');
        Route::delete('/pawn-item-photos/{photo}', [PawnItemController::class, 'destroyPhoto'])->name('pawnItems.photo.destroy');
    });

    // API ដែលប្រើក្នុងការបង្កើតកិច្ចសន្យា
    Route::middleware('can:contracts.manage')->group(function () {
        Route::get('/api/customers/{customer}/pawn-items', [PawnItemController::class, 'byCustomer'])
            ->name('customers.pawnItems');

        Route::get('/api/pawn-item-owner/{item}', function ($item) {
            return response()->json(['customer_id' => PawnItem::findOrFail($item)->customer_id]);
        });
    });

    // ----- Loan Contract -----
    Route::middleware('can:contracts.view')->group(function () {
        Route::get('/loanContract', [LoanContractController::class, 'index'])->name('loanContract');

        Route::get('/loanContract/{contract}', [LoanContractController::class, 'show'])
            ->whereNumber('contract')
            ->name('loanContract.show');

        Route::get('/loan-contracts/data', [LoanContractController::class, 'data'])->name('loan-contracts.data');
        Route::get('/loan-contracts/{contract}/print', [LoanContractController::class, 'print'])->name('loan-contracts.print');
    });

    Route::middleware('can:contracts.manage')->prefix('loan-contracts')->name('loan-contracts.')->group(function () {
        Route::post('/', [LoanContractController::class, 'store'])->name('store');
        Route::post('/{contract}/renew', [LoanContractController::class, 'renew'])->name('renew');
        Route::post('/{contract}/extend', [LoanContractController::class, 'extend'])->name('extend');
        Route::post('/{contract}/redeem', [LoanContractController::class, 'redeem'])->name('redeem');
    });

    Route::post('/loan-contracts/{contract}/auction', [LoanContractController::class, 'auction'])
        ->middleware('can:contracts.auction')->name('loan-contracts.auction');

    // ----- Payments -----
    Route::middleware('can:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('/payments/data', [PaymentController::class, 'data'])->name('payments.data');
    });

    Route::post('/payments/{id}/pay', [PaymentController::class, 'pay'])
        ->middleware('can:payments.manage')->name('payments.pay');

    // ----- Auction & Sales -----
    Route::middleware('can:auctions.view')->group(function () {
        Route::get('/auction-sales', [AuctionController::class, 'index'])->name('auction-sales');
        Route::get('/auction-sales/data', [AuctionController::class, 'data'])->name('auction-sales.data');
    });

    Route::middleware('can:auctions.manage')->group(function () {
        Route::post('/auction-sales/schedule', [AuctionController::class, 'schedule'])->name('auction-sales.schedule');
        Route::post('/auction-sales/{auction}/result', [AuctionController::class, 'result'])->name('auction-sales.result');
        Route::post('/auction-sales/{auction}/cancel', [AuctionController::class, 'cancel'])->name('auction-sales.cancel');
    });

    // ----- Settings + Backup/Restore (Admin) -----
    Route::middleware('can:settings.manage')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::post('/settings/{group}', [SettingController::class, 'save'])->name('settings.save');
        Route::get('/settings/backup/download', [SettingController::class, 'backup'])->name('settings.backup');
        Route::post('/settings/backup/restore', [SettingController::class, 'restore'])->name('settings.restore');
    });

    // ----- Users & Roles (Admin) -----
    Route::middleware('can:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ----- Customer portal -----
    Route::get('/my-pledges', function () {
        return "Welcome to your personal client tracking portal layout view page!";
    })->name('customer.pledges');
});