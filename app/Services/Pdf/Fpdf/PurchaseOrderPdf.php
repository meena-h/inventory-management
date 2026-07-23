<?php

namespace App\Services\Pdf\Fpdf;

use App\Models\PurchaseOrder;
use FPDF;

class PurchaseOrderPdf extends FPDF
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

    public function build(PurchaseOrder $po): void
    {
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 15);
        $this->AddPage();
        $this->header_($po);
        $this->companyAndMeta($po);
        $this->shipToAndSupplier($po);
        $this->itemsTable($po);
        $this->totals($po);
    }

    protected function header_(PurchaseOrder $po): void
    {
        $this->SetFont('Arial', 'B', 28);
        $this->SetTextColor(0, 172, 193);
        $this->SetXY(10, 10);
        $this->Cell(0, 14, 'Purchase Order', 0, 1);

        $this->SetFillColor(245, 166, 35);
        $this->Ellipse(186, 18, 11, 11, 'F');
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(175, 15);
        $this->Cell(22, 6, 'Logo', 0, 0, 'C');

        $this->SetDrawColor(0, 172, 193);
        $this->SetLineWidth(0.7);
        $this->Line(10, 30, 200, 30);

        $this->SetTextColor(0, 0, 0);
        $this->SetXY(10, 36);
    }

    protected function companyAndMeta(PurchaseOrder $po): void
    {
        $startY = $this->GetY();

        // Left — company block
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(105, 6, $this->company['name'], 0, 1);

        $this->SetX(10);
        $this->SetFont('Arial', 'I', 9.5);
        $this->MultiCell(105, 4.5, $this->company['address'] . "\nPhone: " . $this->company['phone']);

        $companyBlockEndY = $this->GetY();

        // Right — PO meta box
        $boxHeight = 38;
        $this->SetFillColor(245, 245, 245);
        $this->Rect(120, $startY, 80, $boxHeight, 'F');
        $this->SetFont('Arial', '', 9.5);

        $rows = [
            'PO no:'                 => $po->order_code,
            'Purchase Order Status:' => ucfirst($po->status->value ?? $po->status),
            'Date:'                  => optional($po->ordered_at)->format('d.m.Y'),
            'Requested By:'          => $po->user->name ?? 'N/A',
            'Approved By:'           => $po->approved_by ?? 'N/A',
            'Department:'            => $po->department ?? 'N/A',
        ];

        $y = $startY + 5;
        foreach ($rows as $label => $value) {
            $this->SetXY(124, $y);
            $this->Cell(46, 5.5, $label);
            $this->Cell(35, 5.5, (string) $value);
            $y += 5.5;
        }

        $this->SetY(max($companyBlockEndY, $startY + $boxHeight) + 8);
    }

   protected function shipToAndSupplier(PurchaseOrder $po): void
{
    $this->SetX(10);
    $this->SetFont('Arial', 'B', 10.5);
    $this->Cell(0, 6, 'Ship to', 0, 1);

    $this->SetX(10);
    $this->SetFont('Arial', 'B', 9.5);
    $this->Cell(0, 5, $this->shipTo['name'], 0, 1);

    $this->SetX(10);
    $this->SetFont('Arial', 'I', 9.5);
    $this->MultiCell(105, 4.5,
        "Attn: {$this->shipTo['attn']}\n{$this->shipTo['address']}\nPhone: {$this->shipTo['phone']}"
    );

    $this->Ln(5);

    // Track Y so Supplier and "Terms of sale" start at the same height
    $rowStartY = $this->GetY();

    // Left column — Supplier
    $supplier = $po->supplier;
    $this->SetXY(10, $rowStartY);
    $this->SetFont('Arial', 'B', 10.5);
    $this->Cell(0, 6, 'Supplier', 0, 1);

    $this->SetX(10);
    $this->SetFont('Arial', 'B', 9.5);
    $this->Cell(0, 5, $supplier->name, 0, 1);

    $this->SetX(10);
    $this->SetFont('Arial', 'I', 9.5);
    $this->MultiCell(105, 4.5,
        ($supplier->address ?? '') . "\n" .
        'Phone: ' . ($supplier->phone ?? '-') . "\n" .
        'Email: ' . ($supplier->email ?? '-') . "\n" .
        'Terms: ' . ($supplier->terms ?? '-')
    );

    $supplierBlockEndY = $this->GetY();

    // Right column — Terms of sale and other comments
    $this->SetXY(120, $rowStartY);
    $this->SetFont('Arial', 'B', 10.5);
    $this->Cell(80, 6, 'Terms of sale and other comments', 0, 1);

    $this->SetXY(120, $rowStartY + 6);
    $this->SetFont('Arial', '', 8.5);
    $this->SetTextColor(150, 150, 150);
    $this->MultiCell(80, 4.5, 'Add any additional instructions or terms here.');
    $this->SetTextColor(0, 0, 0);

    // Move cursor below whichever column is taller
    $this->SetY(max($supplierBlockEndY, $this->GetY()) + 6);
}

    protected function itemsTable(PurchaseOrder $po): void
    {
        $this->SetDrawColor(0, 172, 193);
        $this->SetLineWidth(0.6);
        $y = $this->GetY();
        $this->Line(10, $y, 200, $y);
        $this->Ln(4);

        $this->SetX(10);
        $this->SetFont('Arial', 'B', 10);
        $widths = [28, 62, 18, 18, 32, 32];
        $headers = ['Item #', 'Description', 'Qty', 'Unit', 'Unit Price', 'Total'];
        $aligns  = ['L', 'L', 'R', 'L', 'R', 'R'];

        foreach ($headers as $i => $h) {
            $this->Cell($widths[$i], 9, $h, 0, 0, $aligns[$i]);
        }
        $this->Ln();

        $this->SetX(10);
        $this->SetDrawColor(210, 210, 210);
        $y = $this->GetY();
        $this->Line(10, $y, 200, $y);

        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(70, 70, 70);

        foreach ($po->products as $item) {
            $this->SetX(10);
            $lineTotal = $item->quantity * $item->unit_price;

            $this->Cell($widths[0], 9, $item->product->product_code ?? '-', 0, 0, 'L');
            $this->Cell($widths[1], 9, $item->product->name ?? '-', 0, 0, 'L');
            $this->Cell($widths[2], 9, (string) $item->quantity, 0, 0, 'R');
            $this->Cell($widths[3], 9, $item->product->unit ?? '-', 0, 0, 'L');
            $this->Cell($widths[4], 9, $this->money($item->unit_price), 0, 0, 'R');
            $this->Cell($widths[5], 9, $this->money($lineTotal), 0, 0, 'R');
            $this->Ln();

            $this->SetX(10);
            $this->SetDrawColor(235, 235, 235);
            $y = $this->GetY();
            $this->Line(10, $y, 200, $y);
        }

        $this->SetTextColor(0, 0, 0);
    }

    protected function totals(PurchaseOrder $po): void
    {
        $subtotal = $po->products->sum(fn ($i) => $i->quantity * $i->unit_price);
        $tax = $po->tax ?? 0;
        $shipping = $po->shipping ?? 0;
        $other = $po->other ?? 0;
        $total = $po->total_amount ?? ($subtotal + $tax + $shipping + $other);

        $this->Ln(8);
        $this->SetFont('Arial', 'B', 9.5);

        foreach (['Subtotal' => $subtotal, 'Tax' => $tax, 'Shipping' => $shipping, 'Other' => $other] as $label => $val) {
            $this->SetX(10);
            $this->Cell(150, 6.5, $label, 0, 0, 'R');
            $this->Cell(30, 6.5, $this->money($val), 0, 1, 'R');
        }

        $this->Ln(2);
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 172, 193);
        $this->Cell(150, 8, 'Total', 0, 0, 'R');
        $this->Cell(30, 8, $this->money($total), 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    protected function money(float $amount): string
    {
        return iconv('UTF-8', 'windows-1252', '£ ' . number_format($amount, 2));
    }

    public function Ellipse($x, $y, $rx, $ry, $style = 'D')
    {
        if ($style === 'F') { $op = 'f'; }
        elseif ($style === 'FD' || $style === 'DF') { $op = 'B'; }
        else { $op = 'S'; }

        $lx = 4/3 * (M_SQRT2 - 1) * $rx;
        $ly = 4/3 * (M_SQRT2 - 1) * $ry;
        $k = $this->k;
        $h = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x + $rx) * $k, ($h - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x + $lx) * $k, ($h - ($y + $ry)) * $k,
            $x * $k, ($h - ($y + $ry)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $lx) * $k, ($h - ($y + $ry)) * $k,
            ($x - $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x - $rx) * $k, ($h - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x - $lx) * $k, ($h - ($y - $ry)) * $k,
            $x * $k, ($h - ($y - $ry)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x + $lx) * $k, ($h - ($y - $ry)) * $k,
            ($x + $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x + $rx) * $k, ($h - $y) * $k, $op));
    }
}