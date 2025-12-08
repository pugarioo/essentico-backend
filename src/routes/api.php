<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\AuthController;

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

// User routes
Route::apiResource('users', UserController::class);
// Allow POST for user updates (for FormData compatibility)
Route::post('users/{user}', [UserController::class, 'update'])->name('users.update.post');

// Category routes
Route::apiResource('categories', CategoryController::class);

// Product routes
Route::apiResource('products', ProductController::class);
// Allow POST for product updates (for FormData compatibility)
Route::post('products/{product}', [ProductController::class, 'update'])->name('products.update.post');
// Get product ratings (public)
Route::get('products/{product}/ratings', [RatingController::class, 'getProductRatings']);

// Order routes
Route::apiResource('orders', OrderController::class);

// Order Item routes
Route::apiResource('order-items', OrderItemController::class);

// Discount routes
Route::apiResource('discounts', DiscountController::class);

// Auth routes (public)
Route::post('admin/login', [AuthController::class, 'adminLogin']);
Route::post('user/login', [AuthController::class, 'userLogin']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::match(['put', 'post'], 'user', [UserController::class, 'updateCurrent']);
    
    // Cart routes
    Route::get('cart', [CartItemController::class, 'index']);
    Route::post('cart', [CartItemController::class, 'store']);
    Route::put('cart/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('cart/{cartItem}', [CartItemController::class, 'destroy']);
    
    // Rating routes (authenticated users)
    Route::get('ratings/check', [RatingController::class, 'checkRating']);
    Route::post('ratings', [RatingController::class, 'store']);
    
    // Admin rating routes
    Route::get('admin/ratings', [RatingController::class, 'adminIndex']);
    Route::delete('admin/ratings/{rating}', [RatingController::class, 'adminDestroy']);
});

