<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // GET /api/suppliers
    public function index()
    {
        try {
            $suppliers = Supplier::latest()->get();

            return response()->json([
                'total'     => $suppliers->count(),
                'suppliers' => $suppliers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/suppliers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255|unique:suppliers,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $supplier = Supplier::create($validated);

            return response()->json([
                'message'  => 'Supplier created successfully',
                'supplier' => $supplier,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/suppliers/{id}
    public function show($id)
    {
        try {
            $supplier = Supplier::find($id);

            if (! $supplier) {
                return response()->json(['message' => 'Supplier not found'], 404);
            }

            return response()->json(['supplier' => $supplier]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/suppliers/{id}
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'nullable|email|max:255|unique:suppliers,email,' . $id,
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $supplier->update($validated);

            return response()->json([
                'message'  => 'Supplier updated successfully',
                'supplier' => $supplier,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/suppliers/{id}
    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        try {
            $supplier->delete();

            return response()->json(['message' => 'Supplier deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}