<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/profile',  [AuthController::class, 'profile']);
    Route::post('/logout',  [AuthController::class, 'logout']);

    // Categories
    Route::get('/categories',      [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/suppliers',      [SupplierController::class, 'index']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);

    Route::get('/products',      [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::get('/stocks/current',             [StockController::class, 'currentStock']);
    Route::get('/stocks/low',                 [StockController::class, 'lowStock']);
    Route::get('/stocks/{product_id}/history',[StockController::class, 'history']);
    Route::get('/stocks/{product_id}/current', [StockController::class, 'currentStockByProduct']);

    Route::post('/stocks/in',                 [StockController::class, 'stockIn']);
    Route::post('/stocks/out', [StockController::class, 'stockOut']); 


    Route::get('/purchase-orders',      [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);

    

    Route::get('/reports/master',          [ReportController::class, 'master']);
    Route::get('/reports/stock-movements', [ReportController::class, 'stockMovements']);
    Route::get('/reports/stock-at-date',   [ReportController::class, 'stockAtDate']);

    // Admin only
    Route::middleware('admin')->group(function () {

        // Categories
        Route::post('/categories',        [CategoryController::class, 'store']);
        Route::put('/categories/{id}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Suppliers
        Route::post('/suppliers',        [SupplierController::class, 'store']);
        Route::put('/suppliers/{id}',    [SupplierController::class, 'update']);
        Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

        // Products
        Route::post('/products',        [ProductController::class, 'store']);
        Route::put('/products/{id}',    [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        Route::post('/purchase-orders',              [PurchaseOrderController::class, 'store']);
        Route::put('/purchase-orders/{id}/receive',  [PurchaseOrderController::class, 'receive']);
        Route::put('/purchase-orders/{id}/cancel',   [PurchaseOrderController::class, 'cancel']);
    });
    

});