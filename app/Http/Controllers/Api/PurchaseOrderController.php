<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Services\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    public function __construct(
    protected PurchaseOrderService $purchaseOrderService
    ) {}

    // GET /api/purchase-orders
    public function index()
    {
        try {

            $orders = $this->purchaseOrderService->index();

            return response()->json([
                'total'  => $orders->count(),
                'orders' => $orders,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    // POST /api/purchase-orders
    public function store(StorePurchaseOrderRequest $request)
    {
        try {

            $order = $this->purchaseOrderService->store(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'message' => 'Purchase order created successfully',
                'order' => $order,
            ],201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    // GET /api/purchase-orders/{id}
    public function show(PurchaseOrder $purchaseOrder)
    {
        try {

            return response()->json([
                'order' => $this->purchaseOrderService->show($purchaseOrder),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    // PUT /api/purchase-orders/{id}/receive — Mark as received + auto stock in
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {

            $order = $this->purchaseOrderService->receive(
                $purchaseOrder,
                $request->user()
            );

            return response()->json([
                'message' => 'Purchase order received and stock updated successfully',
                'order' => $order,
            ]);

        } catch (\InvalidArgumentException $e) {

        return response()->json([
            'message' => $e->getMessage(),
        ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/purchase-orders/{id}/cancel
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        try {

            $order = $this->purchaseOrderService->cancel($purchaseOrder);

            return response()->json([
                'message' => 'Purchase order cancelled successfully',
                'order' => $order,
            ]);

        } catch (\InvalidArgumentException $e) {

        return response()->json([
            'message' => $e->getMessage(),
        ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}