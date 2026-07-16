<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use App\Enums\StockTransactionType;
use Illuminate\Validation\Rules\Enum;
use App\Http\Resources\ReportResource;

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
            ])->get();

            return response()->json([
                'total' => $products->count(),
                'products' => ReportResource::collection($products),
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
            'type' => ['sometimes', new Enum(StockTransactionType::class)],
            'from_date'  => 'sometimes|date',
            'to_date'    => 'sometimes|date|after_or_equal:from_date',
            'per_page'   => 'sometimes|integer|min:1|max:100',
        ]);

        try {
            $query = StockTransaction::with([
                'product:id,product_code,name,unit',
                'user:id,name',
            ])
            ->when($request->filled('product_id'), fn ($q) =>
                $q->where('product_id', $request->product_id)
            )
            ->when($request->filled('type'), fn ($q) =>
                $q->where('type', $request->type)
            )
            ->when($request->filled('from_date'), fn ($q) =>
                $q->where('transaction_date', '>=', $request->from_date)
            )
            ->when($request->filled('to_date'), fn ($q) =>
                $q->where('transaction_date', '<=', $request->to_date)
            );

            $summaryTransactions = (clone $query)->get();

            $summary = [
                'total_in' => $summaryTransactions
                    ->where('type', StockTransactionType::IN)
                    ->sum('quantity'),

                'total_out' => $summaryTransactions
                    ->where('type', StockTransactionType::OUT)
                    ->sum('quantity'),
            ];

            // Pagination
            $query->latest('transaction_date');

            $perPage = $request->input('per_page', 10);
            $transactions = $query->paginate($perPage);

            return response()->json([
                'summary'      => $summary,
                'total'        => $transactions->total(),
                'per_page'     => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'transactions' => ReportResource::collection($transactions->getCollection()),
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

            // all transactions in/out up to that date
            $transactions  = StockTransaction::where('product_id', $request->product_id)
                ->where('transaction_date', '<=', $request->date)
                ->get();

            $totalIn = $transactions
                ->where('type', StockTransactionType::IN)
                ->sum('quantity');

            $totalOut = $transactions
                ->where('type', StockTransactionType::OUT)
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