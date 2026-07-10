<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class ProductService
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'suppliers'])->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {

            switch ($request->stock_status) {

                case 'low':
                    $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
                    break;

                case 'in_stock':
                    $query->where('current_stock', '>', 0);
                    break;

                case 'out_of_stock':
                    $query->where('current_stock', 0);
                    break;
            }
        }

        $products = $query->get();

        if ($request->boolean('grouped')) {
            return [
                'total' => $products->count(),
                'categories' => $products
                    ->groupBy(fn ($product) => $product->category->name ?? 'Uncategorized')
                    ->map(fn ($items, $categoryName) => [
                        'category' => $categoryName,
                        'total' => $items->count(),
                        'products' => $items->values(),
                    ])
                    ->values(),
            ];
        }

        return [
            'total' => $products->count(),
            'products' => $products,
        ];
    }

    public function store(array $validated)
    {

        try {

            DB::beginTransaction();

            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $product = Product::create($validated);

            if (!empty($validated['supplier_ids'] ?? [])) {
                $product->suppliers()->attach($validated['supplier_ids']);
            }

            DB::commit();

            return $product->load(['category', 'suppliers']);

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function show(Product $product)
    {
        return $product->load(['category', 'suppliers']);
    }

    public function update(Product $product, array $validated)
    {

        try {

            DB::beginTransaction();

            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $product->update($validated);

            if (isset($validated['supplier_ids'])) {
                $product->suppliers()->sync($validated['supplier_ids']);
            }

            DB::commit();

            return $product->load(['category', 'suppliers']);

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function destroy(Product $product)
    {

        try {

            $product->suppliers()->detach();
            $product->delete();

        } catch (QueryException $e) {
            throw $e;

        } catch (\Exception $e) {
            throw $e;
        }
    }
}