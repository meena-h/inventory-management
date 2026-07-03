<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurrentStock;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\StockTransactionType;

class StockController extends Controller
{
    // POST /api/stocks/in — Add stock in
    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'note'             => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Record the transaction
            $transaction = StockTransaction::create([
                'product_id'       => $validated['product_id'],
                'user_id'          => $request->user()->id,
                'transaction_date' => $validated['transaction_date'],
                'type'             => StockTransactionType::IN,
                'quantity'         => $validated['quantity'],
                'note'             => $validated['note'] ?? null,
            ]);

            // Update or create current stock
            $currentStock = CurrentStock::where('product_id', $validated['product_id'])->first();

            if ($currentStock) {
                $currentStock->current_stock += $validated['quantity'];
                $currentStock->updated_at = now();
                $currentStock->save();
            } else {
                CurrentStock::create([
                    'product_id'    => $validated['product_id'],
                    'current_stock' => $validated['quantity'],
                    'updated_at'    => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message'       => 'Stock added successfully',
                'transaction'   => $transaction->load('product', 'user'),
                'current_stock' => CurrentStock::where('product_id', $validated['product_id'])->first(),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/stocks/current — Current stock for all products
    public function currentStock()
    {
        try {
            $stocks = CurrentStock::with('product:id,product_code,name,unit,low_stock_threshold')
                ->get()
                ->map(function ($stock) {
                    return [
                        'product_id'          => $stock->product_id,
                        'product_code'        => $stock->product->product_code,
                        'product_name'        => $stock->product->name,
                        'unit'                => $stock->product->unit,
                        'current_stock'       => $stock->current_stock,
                        'low_stock_threshold' => $stock->product->low_stock_threshold,
                        'is_low_stock'        => $stock->current_stock <= $stock->product->low_stock_threshold,
                        'last_updated'        => $stock->updated_at,
                    ];
                });

            return response()->json([
                'total'  => $stocks->count(),
                'stocks' => $stocks,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/stocks/low — Products with low stock
    public function lowStock()
    {
        try {
            $stocks = CurrentStock::with('product:id,product_code,name,unit,low_stock_threshold')
                ->get()
                ->filter(function ($stock) {
                    return $stock->current_stock <= $stock->product->low_stock_threshold;
                })
                ->map(function ($stock) {
                    return [
                        'product_id'          => $stock->product_id,
                        'product_code'        => $stock->product->product_code,
                        'product_name'        => $stock->product->name,
                        'unit'                => $stock->product->unit,
                        'current_stock'       => $stock->current_stock,
                        'low_stock_threshold' => $stock->product->low_stock_threshold,
                        'shortage'            => $stock->product->low_stock_threshold - $stock->current_stock,
                    ];
                })->values();

            return response()->json([
                'total'  => $stocks->count(),
                'stocks' => $stocks,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/stocks/{product_id}/history — Stock history for a product
    public function history($product_id)
    {
        try {
            $product = Product::find($product_id);

            if (! $product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $transactions = StockTransaction::where('product_id', $product_id)
                ->with('user:id,name')
                ->latest()
                ->get();

            $currentStock = CurrentStock::where('product_id', $product_id)->first();

            return response()->json([
                'product'       => [
                    'id'           => $product->id,
                    'product_code' => $product->product_code,
                    'name'         => $product->name,
                    'unit'         => $product->unit,
                ],
                'current_stock' => $currentStock ? $currentStock->current_stock : 0,
                'total_transactions' => $transactions->count(),
                'transactions'  => $transactions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/stocks/{product_id}/current
public function currentStockByProduct($product_id)
{
    try {
        // Check if product exists first
        $product = Product::find($product_id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $stock = CurrentStock::with('product:id,product_code,name,unit,low_stock_threshold')
            ->where('product_id', $product_id)
            ->first();

        if (! $stock) {
            return response()->json(['message' => 'No stock record found for this product'], 404);
        }

        return response()->json([
            'stock' => [
                'product_id'          => $stock->product_id,
                'product_code'        => $stock->product->product_code,
                'product_name'        => $stock->product->name,
                'unit'                => $stock->product->unit,
                'current_stock'       => $stock->current_stock,
                'low_stock_threshold' => $stock->product->low_stock_threshold,
                'is_low_stock'        => $stock->current_stock <= $stock->product->low_stock_threshold,
                'last_updated'        => $stock->updated_at,
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    // POST /api/stocks/out — Remove stock out
public function stockOut(Request $request)
{
    $validated = $request->validate([
        'product_id'       => 'required|exists:products,id',
        'quantity'         => 'required|integer|min:1',
        'transaction_date' => 'required|date',
        'note'             => 'nullable|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        // Check current stock
        $currentStock = CurrentStock::where('product_id', $validated['product_id'])->first();

        if (! $currentStock || $currentStock->current_stock < $validated['quantity']) {
            return response()->json([
                'message'       => 'Insufficient stock.',
                'current_stock' => $currentStock ? $currentStock->current_stock : 0,
            ], 422);
        }

        // Record the transaction
        $transaction = StockTransaction::create([
            'product_id'       => $validated['product_id'],
            'user_id'          => $request->user()->id,
            'transaction_date' => $validated['transaction_date'],
            'type'             => StockTransactionType::OUT,
            'quantity'         => $validated['quantity'],
            'note'             => $validated['note'] ?? null,
        ]);

        // Reduce current stock
        $currentStock->current_stock -= $validated['quantity'];
        $currentStock->updated_at = now();
        $currentStock->save();

        DB::commit();

        return response()->json([
            'message'       => 'Stock out recorded successfully',
            'transaction'   => $transaction->load('product', 'user'),
            'current_stock' => $currentStock->current_stock,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
}