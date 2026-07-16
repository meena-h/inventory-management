<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->group(function () {

        // Categories
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Suppliers
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);

        // Products
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Stock
        Route::post('/products/stock-out', [StockController::class, 'stockOut']);

        // Purchase Orders
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::put('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        Route::put('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
     
        // Reports
        Route::get('/reports/master', [ReportController::class, 'master']);
        Route::get('/reports/stock-movements', [ReportController::class, 'stockMovements']);
        Route::get('/reports/stock-at-date', [ReportController::class, 'stockAtDate']);
    });
});