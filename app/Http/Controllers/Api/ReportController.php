<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurrentStock;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // GET /api/reports/master
    // Master report — all products with current stock
    public function master()
    {
        try {
            $products = Product::with([
                'category:id,name',
                'suppliers:id,name',
                'currentStock',
            ])->get()->map(function ($product) {
                return [
                    'product_id'          => $product->id,
                    'product_code'        => $product->product_code,
                    'name'                => $product->name,
                    'sku'                 => $product->sku,
                    'unit'                => $product->unit,
                    'price'               => $product->price,
                    'category'            => $product->category->name ?? '-',
                    'suppliers'           => $product->suppliers->pluck('name'),
                    'current_stock'       => $product->currentStock->current_stock ?? 0,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'is_low_stock'        => ($product->currentStock->current_stock ?? 0) <= $product->low_stock_threshold,
                    'is_active'           => $product->is_active,
                ];
            });

            return response()->json([
                'total'    => $products->count(),
                'products' => $products,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/reports/stock-movements
    // Stock in/out report with filters
    // Filters: product_id, type (in/out), from_date, to_date
    public function stockMovements(Request $request)
    {
        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'type'       => 'sometimes|in:in,out',
            'from_date'  => 'sometimes|date',
            'to_date'    => 'sometimes|date|after_or_equal:from_date',
            'per_page'   => 'sometimes|integer|min:1|max:100',
        ]);

        try {
            $query = StockTransaction::with([
                'product:id,product_code,name,unit',
                'user:id,name',
            ]);

            // Filter by product
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->where('transaction_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->where('transaction_date', '<=', $request->to_date);
            }

            $query->latest('transaction_date');

            $perPage      = $request->input('per_page', 10);
            $transactions = $query->paginate($perPage);

            // Summary
            $summary = [
                'total_in'  => StockTransaction::when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
                    ->when($request->from_date, fn($q) => $q->where('transaction_date', '>=', $request->from_date))
                    ->when($request->to_date, fn($q) => $q->where('transaction_date', '<=', $request->to_date))
                    ->where('type', 'in')->sum('quantity'),

                'total_out' => StockTransaction::when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
                    ->when($request->from_date, fn($q) => $q->where('transaction_date', '>=', $request->from_date))
                    ->when($request->to_date, fn($q) => $q->where('transaction_date', '<=', $request->to_date))
                    ->where('type', 'out')->sum('quantity'),
            ];

            return response()->json([
                'summary'      => $summary,
                'total'        => $transactions->total(),
                'per_page'     => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'transactions' => $transactions->items(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/reports/stock-at-date
    // What was the stock of a product on a particular date?
    // Params: product_id (required), date (required)
    public function stockAtDate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'date'       => 'required|date',
        ]);

        try {
            $product = Product::find($request->product_id);

            // Sum all 'in' transactions up to that date
            $totalIn = StockTransaction::where('product_id', $request->product_id)
                ->where('type', 'in')
                ->where('transaction_date', '<=', $request->date)
                ->sum('quantity');

            // Sum all 'out' transactions up to that date
            $totalOut = StockTransaction::where('product_id', $request->product_id)
                ->where('type', 'out')
                ->where('transaction_date', '<=', $request->date)
                ->sum('quantity');

            $stockAtDate = $totalIn - $totalOut;

            return response()->json([
                'product'      => [
                    'id'           => $product->id,
                    'product_code' => $product->product_code,
                    'name'         => $product->name,
                    'unit'         => $product->unit,
                ],
                'date'         => $request->date,
                'total_in'     => $totalIn,
                'total_out'    => $totalOut,
                'stock_at_date'=> $stockAtDate,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}