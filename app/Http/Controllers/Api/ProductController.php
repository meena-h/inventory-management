<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        try {
            $products = Product::with(['category', 'suppliers'])
                ->latest()
                ->get();

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

    // POST /api/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'         => 'required|exists:categories,id',
            'name'                => 'required|string|max:255|unique:products,name',
            'description'         => 'nullable|string',
            'sku'                 => 'nullable|string|unique:products,sku',
            'unit'                => 'sometimes|string|max:50',
            'price'               => 'required|numeric|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'is_active'           => 'sometimes|boolean',
            'supplier_ids'        => 'sometimes|array',
            'supplier_ids.*'      => 'exists:suppliers,id',
        ]);

        try {
            $product = Product::create($validated);

            // Attach suppliers if provided
            if (! empty($validated['supplier_ids'])) {
                $product->suppliers()->attach($validated['supplier_ids']);
            }

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product->load(['category', 'suppliers']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/products/{id}
    // GET /api/products/{id}
    public function show($id)
    {
        try {
            $product = Product::with(['category', 'suppliers'])->find($id); 

            if (! $product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json(['product' => $product]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'category_id'         => 'sometimes|exists:categories,id',
            'name'                => 'sometimes|string|max:255|unique:products,name,' . $id,
            'description'         => 'nullable|string',
            'sku'                 => 'nullable|string|unique:products,sku,' . $id,
            'unit'                => 'sometimes|string|max:50',
            'price'               => 'sometimes|numeric|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'is_active'           => 'sometimes|boolean',
            'supplier_ids'        => 'sometimes|array',
            'supplier_ids.*'      => 'exists:suppliers,id',
        ]);

        try {
            $product->update($validated);

            // Sync suppliers if provided
            if (isset($validated['supplier_ids'])) {
                $product->suppliers()->sync($validated['supplier_ids']);
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->load(['category', 'suppliers']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        try {
            $product->suppliers()->detach();
            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}