<?php

namespace App\Services\Pdf\DomPdf;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderDomPdfService
{
    protected array $company = [
        'name'    => 'Your Company Name',
        'address' => "77 Hammersmith Road, West Kensington\nLondon, W14 0QH",
        'phone'   => '0208 668 381',
    ];

    protected array $shipTo = [
        'name'    => 'Manchester Office',
        'attn'    => 'Billy Buyer',
        'address' => "43 Customer Road\nManchester, M4 1HS",
        'phone'   => '07394 123456',
    ];

    public function generate(PurchaseOrder $purchaseOrder): string
    {
        $purchaseOrder->load([
            'supplier',
            'user:id,name',
            'products.product:id,product_code,name,unit',
        ]);

        $pdf = Pdf::loadView('pdf.dompdf.purchase-order', [
            'purchaseOrder' => $purchaseOrder,
            'company'       => $this->company,
            'shipTo'        => $this->shipTo,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}