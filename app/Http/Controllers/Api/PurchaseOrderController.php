<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurrentStock;
use App\Models\PurchaseOrder;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    // GET /api/purchase-orders
    public function index()
    {
        try {
            $orders = PurchaseOrder::with([
                'supplier:id,supplier_code,name',
                'user:id,name',
                'products.product:id,product_code,name,unit',
            ])->latest()->get();

            return response()->json([
                'total'  => $orders->count(),
                'orders' => $orders,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/purchase-orders
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'ordered_at'           => 'required|date',
            'note'                 => 'nullable|string|max:500',
            'products'             => 'required|array|min:1',
            'products.*.product_id'=> 'required|exists:products,id',
            'products.*.quantity'  => 'required|integer|min:1',
            'products.*.unit_price'=> 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = collect($validated['products'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Create purchase order
            $order = PurchaseOrder::create([
                'supplier_id'  => $validated['supplier_id'],
                'user_id'      => $request->user()->id,
                'status'       => 'pending',
                'total_amount' => $totalAmount,
                'ordered_at'   => $validated['ordered_at'],
                'note'         => $validated['note'] ?? null,
            ]);

            // Create order products
            foreach ($validated['products'] as $item) {
                $order->products()->create($item);
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'order'   => $order->load([
                    'supplier:id,supplier_code,name',
                    'user:id,name',
                    'products.product:id,product_code,name,unit',
                ]),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/purchase-orders/{id}
    public function show($id)
    {
        try {
            $order = PurchaseOrder::with([
                'supplier:id,supplier_code,name',
                'user:id,name',
                'products.product:id,product_code,name,unit',
            ])->find($id);

            if (! $order) {
                return response()->json(['message' => 'Purchase order not found'], 404);
            }

            return response()->json(['order' => $order]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/purchase-orders/{id}/receive — Mark as received + auto stock in
    public function receive(Request $request, $id)
    {
        $order = PurchaseOrder::with('products')->find($id);

        if (! $order) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        if ($order->status === 'received') {
            return response()->json(['message' => 'Order already received'], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Cannot receive a cancelled order'], 422);
        }

        try {
            DB::beginTransaction();

            // Mark order as received
            $order->update([
                'status'      => 'received',
                'received_at' => now()->toDateString(),
            ]);

            // Auto stock in for each product
            foreach ($order->products as $item) {
                // Record stock transaction
                StockTransaction::create([
                    'product_id'       => $item->product_id,
                    'user_id'          => $request->user()->id,
                    'transaction_date' => now()->toDateString(),
                    'type'             => 'in',
                    'quantity'         => $item->quantity,
                    'note'             => 'Auto stock in from Purchase Order ' . $order->order_code,
                ]);

                // Update current stock
                $currentStock = CurrentStock::where('product_id', $item->product_id)->first();

                if ($currentStock) {
                    $currentStock->current_stock += $item->quantity;
                    $currentStock->updated_at = now();
                    $currentStock->save();
                } else {
                    CurrentStock::create([
                        'product_id'    => $item->product_id,
                        'current_stock' => $item->quantity,
                        'updated_at'    => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order received and stock updated successfully',
                'order'   => $order->load([
                    'supplier:id,supplier_code,name',
                    'user:id,name',
                    'products.product:id,product_code,name,unit',
                ]),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/purchase-orders/{id}/cancel
    public function cancel($id)
    {
        $order = PurchaseOrder::find($id);

        if (! $order) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        if ($order->status === 'received') {
            return response()->json(['message' => 'Cannot cancel a received order'], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Order already cancelled'], 422);
        }

        try {
            $order->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Purchase order cancelled successfully',
                'order'   => $order,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}