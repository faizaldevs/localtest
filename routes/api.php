<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Staff;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/staff/{staff}/suppliers', [\App\Http\Controllers\StaffController::class, 'suppliers']);
    Route::get('/product-collections/check-existing', [\App\Http\Controllers\ProductCollectionController::class, 'checkExisting']);
});

Route::get('/staff', function () {
    return Staff::select('id', 'name')->get();
});
