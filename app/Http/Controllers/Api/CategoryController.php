<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    // GET /api/categories
        public function index()
    {
        try {
            $categories = Category::latest()->get();

            return response()->json([
                'total' => $categories->count(),
                'categories' => CategoryResource::collection($categories),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/categories
    public function store(CategoryRequest $request)
    {
        $validated = $request->validated();

        try {
            $category = Category::create($validated);

            return response()->json([
                'message'  => 'Category created successfully',
                'category' => new CategoryResource($category),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/categories/{id}
        public function show(Category $category)
    {
        try {
            
            return new CategoryResource($category);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/categories/{id}
    public function update(CategoryRequest $request, Category $category)
    {

        $validated = $request->validated();

        try {
            $category->update($validated);

            return response()->json([
                'message'  => 'Category updated successfully',
                'category' => new CategoryResource($category),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/categories/{id}
    public function destroy(Category $category)
    {
        try {

            if ($category->products()->exists()) {
                return response()->json([
                    'message' => 'This category cannot be deleted because it has products linked to it. Please remove or reassign the products first.',
                ], 409);
            }

            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}