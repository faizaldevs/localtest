<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProductTransferController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'counts' => [
            'locations' => \App\Models\Location::count(),
            'staff' => \App\Models\Staff::count(),
            'customers' => \App\Models\Customer::count(),
            'suppliers' => \App\Models\Supplier::count(),
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('locations', \App\Http\Controllers\LocationController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('products', \App\Http\Controllers\ProductController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::resource('product-collections', \App\Http\Controllers\ProductCollectionController::class);
    Route::resource('product-transfers', ProductTransferController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('product-sales', ProductSaleController::class);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Supplier Payments Routes
    Route::get('/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
    Route::get('/api/supplier-payments/get-suppliers', [SupplierPaymentController::class, 'getSuppliers'])->name('supplier-payments.get-suppliers');
    Route::get('/api/supplier-payments/get-existing-payments', [SupplierPaymentController::class, 'getExistingPayments'])->name('supplier-payments.get-existing-payments');
    Route::post('/api/supplier-payments/store', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');

    // Customer Payments Routes
    Route::get('/customer-payments/create', [CustomerPaymentController::class, 'create'])->name('customer-payments.create');
    Route::get('/api/customer-payments/get-customers', [CustomerPaymentController::class, 'getCustomers'])->name('customer-payments.get-customers');
    Route::get('/api/customer-payments/get-existing-payments', [CustomerPaymentController::class, 'getExistingPayments'])->name('customer-payments.get-existing-payments');
    Route::post('/api/customer-payments/store', [CustomerPaymentController::class, 'store'])->name('customer-payments.store');

    // Customer Prepaid Payments Routes
    Route::get('/customer-prepaid-payments/create', [CustomerPaymentController::class, 'createPrepaid'])->name('customer-prepaid-payments.create');
    Route::get('/api/staff/{staffId}/customers-with-prepaid', [CustomerPaymentController::class, 'getCustomersWithPrepaid']);
    Route::post('/api/customer-prepaid-payments/store', [CustomerPaymentController::class, 'storePrepaid'])->name('customer-prepaid-payments.store');

});

require __DIR__.'/auth.php';
