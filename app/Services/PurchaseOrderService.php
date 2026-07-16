<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Enums\StockTransactionType;
use App\Models\PurchaseOrder;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    /**
     * Get all purchase orders.
     */
    public function index()
    {
        return PurchaseOrder::with([
            'supplier:id,supplier_code,name',
            'user:id,name',
            'products.product:id,product_code,name,unit',
        ])->latest()->get();
    }

    /**
     * Create a purchase order.
     */
    public function store(array $validated, User $user)
    {

        try {

            $totalAmount = collect($validated['products'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id'  => $validated['supplier_id'],
                'user_id'      => $user->id,
                'status'       => PurchaseOrderStatus::PENDING,
                'total_amount' => $totalAmount,
                'ordered_at'   => $validated['ordered_at'],
                'note'         => $validated['note'] ?? null,
            ]);

            foreach ($validated['products'] as $item) {
                $purchaseOrder->products()->create($item);
            }

            return $purchaseOrder->load([
                'supplier:id,supplier_code,name',
                'user:id,name',
                'products.product:id,product_code,name,unit',
            ]);

        } catch (\Exception $e) {

            throw $e;
        }
    }

    /**
     * Show purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->load([
            'supplier:id,supplier_code,name',
            'user:id,name',
            'products.product:id,product_code,name,unit',
        ]);
    }

    /**
     * Receive purchase order.
     */
    public function receive(PurchaseOrder $purchaseOrder, User $user)
    {
        $purchaseOrder->load('products.product');

        if ($purchaseOrder->status === PurchaseOrderStatus::RECEIVED) {
            throw new \Exception('Order already received.');
        }

        if ($purchaseOrder->status === PurchaseOrderStatus::CANCELLED) {
            throw new \Exception('Cannot receive a cancelled order.');
        }

        try {

            $purchaseOrder->update([
                'status' => PurchaseOrderStatus::RECEIVED,
                'received_at' => now()->toDateString(),
            ]);

            foreach ($purchaseOrder->products as $item) {

                StockTransaction::create([
                    'product_id'       => $item->product_id,
                    'user_id'          => $user->id,
                    'transaction_date' => now()->toDateString(),
                    'type'             => StockTransactionType::IN,
                    'quantity'         => $item->quantity,
                    'note'             => 'Auto stock in from Purchase Order ' . $purchaseOrder->order_code,
                ]);
                
                $item->product->increment('current_stock', $item->quantity);
            }

            return $purchaseOrder->load([
                'supplier:id,supplier_code,name',
                'user:id,name',
                'products.product:id,product_code,name,unit',
            ]);

        } catch (\Exception $e) {


            throw $e;
        }
    }

    /**
     * Cancel purchase order.
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === PurchaseOrderStatus::RECEIVED) {
            throw new \Exception('Cannot cancel a received order.');
        }

        if ($purchaseOrder->status === PurchaseOrderStatus::CANCELLED) {
            throw new \Exception('Order already cancelled.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::CANCELLED,
        ]);

        return $purchaseOrder;
    }
}