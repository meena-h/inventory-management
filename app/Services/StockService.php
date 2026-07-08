<?php

namespace App\Services;

use App\Enums\StockTransactionType;
use App\Models\CurrentStock;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Stock In
     */
    public function stockIn(array $validated, User $user)
    {
        DB::beginTransaction();

        try {

            $transaction = StockTransaction::create([
                'product_id'       => $validated['product_id'],
                'user_id'          => $user->id,
                'transaction_date' => $validated['transaction_date'],
                'type'             => StockTransactionType::IN,
                'quantity'         => $validated['quantity'],
                'note'             => $validated['note'] ?? null,
            ]);

            $currentStock = CurrentStock::firstOrCreate(
                ['product_id' => $validated['product_id']],
                ['current_stock' => 0]
            );

            $currentStock->increment('current_stock', $validated['quantity']);

            DB::commit();

            return [
                'transaction'   => $transaction->load('product', 'user'),
                'current_stock' => $currentStock->fresh(),
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Current Stock
     */
    public function currentStock()
    {
        return CurrentStock::with('product:id,product_code,name,unit,low_stock_threshold')
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
    }

    /**
     * Low Stock
     */
    public function lowStock()
    {
        return CurrentStock::with('product:id,product_code,name,unit,low_stock_threshold')
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
            })
            ->values();
    }

    /**
     * Product History
     */
    public function history(Product $product)
    {
        $transactions = StockTransaction::where('product_id', $product->id)
            ->with('user:id,name')
            ->latest()
            ->get();

        $currentStock = CurrentStock::where('product_id', $product->id)->first();

        return [
            'product' => [
                'id'           => $product->id,
                'product_code' => $product->product_code,
                'name'         => $product->name,
                'unit'         => $product->unit,
            ],
            'current_stock' => $currentStock?->current_stock ?? 0,
            'total_transactions' => $transactions->count(),
            'transactions' => $transactions,
        ];
    }

    /**
     * Current Stock By Product
     */
    public function currentStockByProduct(Product $product)
    {
        $stock = CurrentStock::with('product:id,product_code,name,unit,low_stock_threshold')
            ->where('product_id', $product->id)
            ->first();

        if (!$stock) {
            return null;
        }

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
    }

    /**
     * Stock Out
     */
    public function stockOut(array $validated, User $user)
    {
        DB::beginTransaction();

        try {

            $currentStock = CurrentStock::where('product_id', $validated['product_id'])->first();

            if (!$currentStock || $currentStock->current_stock < $validated['quantity']) {
                throw new \InvalidArgumentException('Insufficient stock.');
            }

            $transaction = StockTransaction::create([
                'product_id'       => $validated['product_id'],
                'user_id'          => $user->id,
                'transaction_date' => $validated['transaction_date'],
                'type'             => StockTransactionType::OUT,
                'quantity'         => $validated['quantity'],
                'note'             => $validated['note'] ?? null,
            ]);

            $currentStock->decrement('current_stock', $validated['quantity']);

            DB::commit();

            return [
                'transaction' => $transaction->load('product', 'user'),
                'current_stock' => $currentStock->fresh()->current_stock,
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}