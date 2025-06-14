<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProductTransferController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\SupplierLoanController;
use App\Http\Controllers\CounterSaleController;
use App\Http\Controllers\StaffLoanController;
use App\Http\Controllers\StaffPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use App\Models\Staff;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierReportController;
use App\Http\Controllers\CustomerReportController;
use App\Http\Controllers\CustomerProductReportController;

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
    Route::resource('staff-loans', StaffLoanController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('products', \App\Http\Controllers\ProductController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::get('/product-collections/check-existing', [\App\Http\Controllers\ProductCollectionController::class, 'checkExisting']);
    
    Route::resource('product-collections', \App\Http\Controllers\ProductCollectionController::class);
    Route::resource('product-transfers', ProductTransferController::class);
    Route::resource('customers', CustomerController::class);
    
    // Custom routes should be before the resource route
    Route::get('/product-sales/check-existing', [\App\Http\Controllers\ProductSaleController::class, 'checkExisting']);
    Route::resource('product-sales', ProductSaleController::class);
    
    // Moved from api.php
    Route::get('/staff/{staff}/suppliers', [\App\Http\Controllers\StaffController::class, 'suppliers']);
    Route::get('/staff/{staff}/customers', function (Staff $staff) {
        return $staff->customers;
    });
    Route::get('/staff-list', function () {
        return Staff::select('id', 'name')->get();
    })->name('staff.list');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Reports Routes
    Route::get('/reports/supplier-product', [ReportController::class, 'supplierProduct'])->name('reports.supplier-product');
    Route::post('/reports/supplier-product/generate', [ReportController::class, 'generateSupplierProductReport'])->name('reports.supplier-product.generate');
    Route::get('/reports/staff-product', [ReportController::class, 'staffProduct'])->name('reports.staff-product');
    Route::post('/reports/staff-product/generate', [ReportController::class, 'generateStaffProductReport'])->name('reports.staff-product.generate');
    Route::get('/reports/customer-product', [CustomerProductReportController::class, 'show'])->name('reports.customer-product');
    Route::post('/reports/customer-product/generate', [CustomerProductReportController::class, 'generate'])->name('reports.customer-product.generate');

    // Supplier Payments Routes
    Route::get('/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
    Route::get('/supplier-payments/get-suppliers', [SupplierPaymentController::class, 'getSuppliers'])->name('supplier-payments.get-suppliers');
    Route::get('/supplier-payments/get-existing-payments', [SupplierPaymentController::class, 'getExistingPayments'])->name('supplier-payments.get-existing-payments');
    Route::post('/supplier-payments/store', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');

    // Customer Payments Routes
    Route::get('/customer-payments/create', [CustomerPaymentController::class, 'create'])->name('customer-payments.create');
    Route::get('/customer-payments/get-customers', [CustomerPaymentController::class, 'getCustomers'])->name('customer-payments.get-customers');
    Route::get('/customer-payments/get-existing-payments', [CustomerPaymentController::class, 'getExistingPayments'])->name('customer-payments.get-existing-payments');
    Route::post('/api/customer-payments/store', [CustomerPaymentController::class, 'store'])->name('customer-payments.store');

    // Customer Prepaid Payments Routes
    Route::get('/customer-prepaid-payments/create', [CustomerPaymentController::class, 'createPrepaid'])->name('customer-prepaid-payments.create');
    Route::get('/api/staff/{staffId}/customers-with-prepaid', [CustomerPaymentController::class, 'getCustomersWithPrepaid']);
    Route::post('/api/customer-prepaid-payments/store', [CustomerPaymentController::class, 'storePrepaid'])->name('customer-prepaid-payments.store');

    // Supplier Loans Routes
    Route::resource('supplier-loans', SupplierLoanController::class);
    Route::resource('staff-loans', StaffLoanController::class);
    
    // Staff Payments Routes
    Route::get('/staff-payments/get-staff-data', [StaffPaymentController::class, 'getStaffPaymentData'])->name('staff-payments.get-staff-data');
    Route::resource('staff-payments', StaffPaymentController::class);
    
    // Counter Sales Routes
    Route::resource('counter-sales', CounterSaleController::class);

    // Staff Cash Transfer Routes
    Route::resource('staff-cash-transfers', \App\Http\Controllers\StaffCashTransferController::class);

    // Reports Routes
    Route::get('/reports/staff-payment', [\App\Http\Controllers\ReportController::class, 'staffPayment'])->name('reports.staff-payment');
    Route::post('/reports/staff-payment/generate', [\App\Http\Controllers\ReportController::class, 'generateStaffPaymentReport'])->name('reports.staff-payment.generate');
    Route::get('/reports/staff-product', [\App\Http\Controllers\ReportController::class, 'staffProduct'])->name('reports.staff-product');
    Route::post('/reports/staff-product/generate', [\App\Http\Controllers\ReportController::class, 'generateStaffProductReport'])->name('reports.staff-product.generate');
    Route::get('/reports/supplier-product', [\App\Http\Controllers\ReportController::class, 'supplierProduct'])->name('reports.supplier-product');
    Route::post('/reports/supplier-product/generate', [\App\Http\Controllers\ReportController::class, 'generateSupplierProductReport'])->name('reports.supplier-product.generate');

    // Staff Payment Routes
    Route::get('supplier-reports', [SupplierReportController::class, 'show'])->name('supplier-reports.show');
    Route::get('customer-reports', [CustomerReportController::class, 'show'])->name('customer-reports.show');

});

require __DIR__.'/auth.php';
