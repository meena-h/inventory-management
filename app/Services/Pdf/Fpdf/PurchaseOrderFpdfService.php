<?php

namespace App\Services\Pdf\Fpdf;

use App\Models\PurchaseOrder;

class PurchaseOrderFpdfService
{
    public function generate(PurchaseOrder $purchaseOrder): string
    {
        $purchaseOrder->load([
            'supplier',
            'user:id,name',
            'products.product:id,product_code,name,unit',
        ]);

        $pdf = new PurchaseOrderPdf();
        $pdf->build($purchaseOrder);

        return $pdf->Output('S'); 
    }
}