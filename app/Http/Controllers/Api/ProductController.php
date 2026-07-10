<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ProductController extends Controller
{
    // GET /api/products
    // GET /products?stock_status=low
    // GET /products?stock_status=in_stock
    // GET /products?stock_status=out_of_stock
    // GET /products?category_id=1&stock_status=low
    // GET /products?grouped=true&stock_status=low
    
     public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request)
    {
        try {

            return response()->json(
                $this->productService->index($request)
            );

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/products
    public function store(StoreProductRequest $request)
    {
        try {

            $product = $this->productService->store(
                $request->validated()
            );

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/products/{id}
    public function show(Product $product)
    {
        try {

            return response()->json([
                'product' => $this->productService->show($product),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/products/{id}
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {

            $product = $this->productService->update(
                $product,
                $request->validated()
            );

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/products/{id}
    public function destroy(Product $product)
    {
        try {

            $this->productService->destroy($product);

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);

        } catch (QueryException $e) {

            if ($e->getCode() === '23000') {

                return response()->json([
                    'message' => 'This product cannot be deleted because it is linked to existing stock or order records. Please remove the related records first.',
                ], 409);
            }

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}