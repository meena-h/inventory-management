<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    // GET /api/categories
        public function index()
    {
        try {
            $categories = Category::latest()->get();

            return response()->json([
                'total'      => $categories->count(),
                'categories' => $categories,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/categories
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        try {
            $category = Category::create($validated);

            return response()->json([
                'message'  => 'Category created successfully',
                'category' => $category,
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
            return response()->json([
                'category' => $category,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/categories/{id}
    public function update(UpdateCategoryRequest $request, Category $category)
    {

        $validated = $request->validated();

        try {
            $category->update($validated);

            return response()->json([
                'message'  => 'Category updated successfully',
                'category' => $category,
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
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully']);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'This category cannot be deleted because it has products linked to it. Please remove or reassign the products first.',
                ], 409);
            }

            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);


        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}