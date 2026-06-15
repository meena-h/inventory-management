<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/categories
        public function index()
    {
        try {
            $categories = Category::latest()->get(); // 👈 removed withCount

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'sometimes|boolean',
        ]);

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
        public function show($id)
    {
        try {
            $category = Category::find($id); // 👈 removed withCount

            if (! $category) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            return response()->json(['category' => $category]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/categories/{id}
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'sometimes|boolean',
        ]);

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
    public function destroy($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        try {
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}