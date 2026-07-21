<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use App\Http\Resources\PurchaseOrderResource;
use Illuminate\Support\Facades\DB;
use App\Services\Pdf\PurchaseOrderPdfService;

class PurchaseOrderController extends Controller
{
    public function __construct(
    protected PurchaseOrderService $purchaseOrderService,
    protected PurchaseOrderPdfService $purchaseOrderPdfService,
    ) {}

    // GET /api/purchase-orders
    public function index()
    {
        try {

            $orders = $this->purchaseOrderService->index();

            return response()->json([
                'total' => $orders->count(),
                'orders' => PurchaseOrderResource::collection($orders),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        try {
            $pdfContent = $this->purchaseOrderPdfService->generate($purchaseOrder);

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="PO-' . $purchaseOrder->order_code . '.pdf"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/purchase-orders
    public function store(StorePurchaseOrderRequest $request)
    {
        try {
    
            DB::beginTransaction();

            $order = $this->purchaseOrderService->store(
                $request->validated(),
                $request->user()
            );

            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'order' => new PurchaseOrderResource($order),
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

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

            $order = $this->purchaseOrderService->show($purchaseOrder);

            return response()->json([
                'order' => new PurchaseOrderResource($order),
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

            DB::beginTransaction();


            $order = $this->purchaseOrderService->receive(
                $purchaseOrder,
                $request->user()
            );

            DB::commit();

            return response()->json([
                'message' => 'Purchase order received and stock updated successfully',
                'order' => new PurchaseOrderResource($order),
            ]);

        } catch (\InvalidArgumentException $e) {

            DB::rollBack();

        return response()->json([
            'message' => $e->getMessage(),
        ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

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
                'order' => new PurchaseOrderResource($order),
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