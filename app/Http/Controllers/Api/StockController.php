<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StockTransactionRequest;
use App\Services\StockService;

class StockController extends Controller
{
    public function __construct(
    protected StockService $stockService
    ) {}


    // GET /api/stocks/current — Current stock for all products
    public function currentStock()
    {
        try {

            $stocks = $this->stockService->currentStock();

            return response()->json([
                'total' => $stocks->count(),
                'stocks' => $stocks,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/stocks/low — Products with low stock
    public function lowStock()
    {
        try {
            $stocks = $this->stockService->lowStock();

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
    public function history(Product $product)
    {
        try {

            $data = $this->stockService->history($product);

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/stocks/out — Remove stock out
public function stockOut(StockTransactionRequest $request)
{
    try {

        $result = $this->stockService->stockOut(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Stock out recorded successfully',
            'transaction' => $result['transaction'],
            'current_stock' => $result['current_stock'],
        ], 201);

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