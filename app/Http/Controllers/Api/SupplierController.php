<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Support\Facades\DB;

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
    public function store(StoreSupplierRequest $request)
    {
        $validated = $request->validated();

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
    public function show(Supplier $supplier)
    {
        try {

            return response()->json(['supplier' => $supplier]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/suppliers/{id}
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $validated = $request->validated();

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
    public function destroy(Supplier $supplier)
    {
        try {

            $supplier->delete();

            return response()->json([
                'message' => 'Supplier deleted successfully'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {

            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'This supplier cannot be deleted because it is linked to existing purchase orders.',
                ], 409);
            }

            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}