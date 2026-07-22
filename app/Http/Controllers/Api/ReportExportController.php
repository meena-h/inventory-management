<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductMasterReportRequest;
use App\Exports\ProductMasterReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    // GET /api/reports/product-master/export
    public function productMaster(ProductMasterReportRequest $request)
    {
        try {
            $filters = array_filter(
                $request->validated(),
                fn ($value) => !is_null($value)
            );

            return Excel::download(
                new ProductMasterReportExport($filters),
                'product-master-report-' . now()->format('Y-m-d') . '.xlsx'
            );

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}