<?php

namespace App\Services\Pdf;

use App\Models\PurchaseOrder;

class PurchaseOrderPdfService
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