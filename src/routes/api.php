<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
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

// Category routes
Route::apiResource('categories', CategoryController::class);

// Product routes
Route::apiResource('products', ProductController::class);

// Order routes
Route::apiResource('orders', OrderController::class);

// Order Item routes
Route::apiResource('order-items', OrderItemController::class);

// Auth routes
Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::match(['put', 'post'], 'user', [UserController::class, 'updateCurrent']); 
});

//test

/*
|--------------------------------------------------------------------------
| Available API Endpoints
|--------------------------------------------------------------------------
|
| Users:
|   GET    /api/users           - List all users
|   POST   /api/users           - Create a new user
|   GET    /api/users/{id}      - Show a specific user with orders
|   PUT    /api/users/{id}      - Update a user
|   DELETE /api/users/{id}      - Delete a user
|
| Categories:
|   GET    /api/categories      - List all categories with product count
|   POST   /api/categories      - Create a new category
|   GET    /api/categories/{id} - Show a specific category with products
|   PUT    /api/categories/{id} - Update a category
|   DELETE /api/categories/{id} - Delete a category
|
| Products:
|   GET    /api/products         - List all products with category
|   POST   /api/products         - Create a new product
|   GET    /api/products/{id}    - Show a specific product with category
|   PUT    /api/products/{id}    - Update a product
|   DELETE /api/products/{id}    - Delete a product
|
| Orders:
|   GET    /api/orders           - List all orders with user and items
|   POST   /api/orders           - Create a new order
|   GET    /api/orders/{id}      - Show a specific order with details
|   PUT    /api/orders/{id}      - Update an order
|   DELETE /api/orders/{id}      - Delete an order
|
| Order Items:
|   GET    /api/order-items      - List all order items
|   POST   /api/order-items      - Create a new order item
|   GET    /api/order-items/{id} - Show a specific order item
|   PUT    /api/order-items/{id} - Update an order item
|   DELETE /api/order-items/{id} - Delete an order item
|
*/
