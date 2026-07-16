<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\DB;

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

            $data = $this->productService->index($request);

            if (isset($data['products'])) {
                $data['products'] = ProductResource::collection($data['products']);
            }

            if (isset($data['categories'])) {
                $data['categories'] = collect($data['categories'])->map(function ($category) {
                    $category['products'] = ProductResource::collection($category['products']);
                    return $category;
                });
            }

            return response()->json($data);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/products
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $product = $this->productService->store(
                $request->validated()
            );

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully',
                'product' => new ProductResource($product),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
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
                'product' => new ProductResource(
                    $this->productService->show($product)
                ),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/products/{id}
    public function update(ProductRequest $request, Product $product)
    {
        try {

            DB::beginTransaction();

            $product = $this->productService->update(
                $product,
                $request->validated()
            );

            DB::commit();

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => new ProductResource($product),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

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

            DB::beginTransaction();

            $this->productService->destroy($product);

            DB::commit();

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);

        } catch (\Exception $e) {
   
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }
    }
}