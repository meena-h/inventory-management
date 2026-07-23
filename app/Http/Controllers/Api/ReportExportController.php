<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductMasterReportRequest;
use App\Exports\ProductMasterReport;
use App\Services\Reports\ProductMasterReportService;

class ReportExportController extends Controller
{
    public function __construct(
        protected ProductMasterReportService $productMasterReportService
    ) {}

    // GET /api/reports/product-master/export
    public function productMaster(ProductMasterReportRequest $request)
    {
        try {
            $filters = array_filter(
                $request->validated(),
                fn ($value) => !is_null($value)
            );

            $query = $this->productMasterReportService->query($filters);

            $export = new ProductMasterReport($query);

            return $export->download(
                'product-master-report-' . now()->format('Y-m-d-His') . '.xlsx'
            );

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}