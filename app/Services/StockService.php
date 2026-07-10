<?php

namespace App\Services;

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockService
{

    /**
     * Current Stock
     */
    public function currentStock()
    {
        return Product::select(
                'id',
                'product_code',
                'name',
                'unit',
                'current_stock',
                'low_stock_threshold',
                'updated_at'
            )
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'current_stock' => $product->current_stock,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'is_low_stock' => $product->current_stock <= $product->low_stock_threshold,
                    'last_updated' => $product->updated_at,
                ];
            });
    }

    /**
     * Low Stock
     */
    public function lowStock()
    {
        return Product::whereColumn('current_stock', '<=', 'low_stock_threshold')
        ->get()
        ->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'current_stock' => $product->current_stock,
                'low_stock_threshold' => $product->low_stock_threshold,
                'shortage' => $product->low_stock_threshold - $product->current_stock,
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

        return [
            'product' => [
                'id'           => $product->id,
                'product_code' => $product->product_code,
                'name'         => $product->name,
                'unit'         => $product->unit,
            ],
            'current_stock' => $product->current_stock,
            'total_transactions' => $transactions->count(),
            'transactions' => $transactions,
        ];
    }


    /**
     * Stock Out
     */
    public function stockOut(array $validated, User $user)
    {
        DB::beginTransaction();

        try {

            $product = Product::findOrFail($validated['product_id']);

            if ($product->current_stock < $validated['quantity']) {
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

            $product->decrement('current_stock', $validated['quantity']);

            DB::commit();

            return [
                'transaction' => $transaction->load('product', 'user'),
                'current_stock' => $product->fresh()->current_stock,
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}