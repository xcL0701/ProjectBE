<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    ProductController,
    MachineController,
    CartController
};
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\UserController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api_key')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])
     ->where('product', '[a-z0-9-]+');

    // Machines
    Route::get('/machine', [MachineController::class, 'index']);
    Route::get('/machine/{machine:slug}', [MachineController::class, 'show']);
});

Route::middleware(['api_key', 'auth:sanctum'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::put('/cart/update-item/{id}', [CartController::class, 'updateItem']);
    Route::post('/cart/shipping', [CartController::class, 'updateShipping']);
    Route::delete('/cart/remove-item/{id}', [CartController::class, 'removeItem']);

    // Orders
    Route::get('/orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/orders/{id}/payments', [OrderController::class, 'payments']);

    // Payments
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payment/confirmation/{token}', [\App\Http\Controllers\Api\PaymentController::class, 'show'])->name('payment.confirm');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user', [UserController::class, 'update']);

    Route::post('/products-like', [LikeController::class, 'toggle']);
    Route::get('/products-like', [LikeController::class, 'index']);
});

Route::get('/products/like-test', function () {
    return 'OK';
});
