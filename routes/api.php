<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BusinessCustomerController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\CutOffController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('businesses/register', [AuthController::class, 'register']);
Route::post('businesses/login', [AuthController::class, 'login']);
Route::post('businesses/logout', [AuthController::class, 'logout'])->middleware('auth:business');

Route::middleware('auth:business')->group(function () {

    Route::controller(ProductController::class)->group(function () {
        Route::get('products', 'index');
        Route::get('products/{id}', 'show');
        Route::post('products', 'store');
        Route::put('products/{id}', 'update');
        Route::delete('products/{id}', 'delete');
        Route::patch('products/{id}/restored', 'restore');
    });

    Route::controller(BusinessCustomerController::class)->group(function() {
        Route::get('customers', 'index');
        Route::get('customers/{id}', 'show');
        Route::post('customers', 'store');
        Route::put('customers/{id}', 'update');
        Route::delete('customers/{id}', 'delete');
        Route::patch('customers/{id}/restored', 'restore');
    });

    Route::controller(CreditController::class)->group(function() {
        Route::get('credits', 'index');
        Route::get('credits/{id}', 'show');
        Route::post('credits', 'store');
        Route::put('credits/{id}', 'update');
        Route::delete('credits/{id}', 'delete');
        Route::patch('credits/{id}/restored', 'restore');
    });

    Route::controller(PaymentController::class)->group(function() {
        Route::get('payments', 'index');
        Route::get('payments/{id}', 'show');
        Route::post('payments', 'store');
        Route::put('payments/{id}', 'update');
        Route::delete('payments/{id}', 'delete');
    });

    Route::controller(CutOffController::class)->group(function () {
        Route::get('cutoffs', 'index');
        Route::post('customers/{customerId}/cutoffs', 'storeCustomerCutOff');
    });

    Route::get('test', function () {
        $products = Product::whereBetween('created_at', [Carbon::createFromTimestamp(0), Carbon::now()])->get();
        return response()->json([
            'products' => $products,
        ]);
    });

});

