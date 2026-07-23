<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;  
use App\Http\Controllers\Api\ReportExportController;

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Suppliers
Route::get('/suppliers', [SupplierController::class, 'index']);
Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Stocks
Route::get('/products/current-stock', [StockController::class, 'currentStock']);
Route::get('/products/low-stock', [StockController::class, 'lowStock']);
Route::get('/products/{product}/transactions', [StockController::class, 'history']);

// Purchase Orders
Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);

//Product Master Details Excel Report 
Route::get('/reports/product-master/export', [ReportExportController::class, 'productMaster']);

//Download Purchase Order PDF
Route::get('/purchase-orders/{purchaseOrder}/pdf/fpdf', [PurchaseOrderController::class, 'downloadFpdf']);
Route::get('/purchase-orders/{purchaseOrder}/pdf/dompdf', [PurchaseOrderController::class, 'downloadDomPdf']);