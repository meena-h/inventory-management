<?php

namespace App\Services\Pdf;

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
        $this->AddPage();
        $this->header_($po);
        $this->companyAndMeta($po);
        $this->shipToAndSupplier($po);
        $this->itemsTable($po);
        $this->totals($po);
    }

    protected function header_(PurchaseOrder $po): void
    {
        $this->SetFont('Arial', 'B', 26);
        $this->SetTextColor(0, 172, 193);
        $this->SetXY(10, 10);
        $this->Cell(0, 15, 'Purchase Order', 0, 1);

        $this->SetFillColor(245, 166, 35);
        $this->Ellipse(185, 20, 12, 12, 'F');
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(173, 17);
        $this->Cell(24, 6, 'Logo', 0, 0, 'C');

        $this->SetDrawColor(0, 172, 193);
        $this->SetLineWidth(0.6);
        $this->Line(10, 32, 200, 32);

        $this->SetTextColor(0, 0, 0);
        $this->SetXY(10, 40);
    }

    protected function companyAndMeta(PurchaseOrder $po): void
    {
        $startY = $this->GetY();

        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 6, $this->company['name'], 0, 1);
        $this->SetFont('Arial', 'I', 9);
        $this->MultiCell(100, 5, $this->company['address'] . "\nPhone: " . $this->company['phone']);

        $this->SetFillColor(245, 245, 245);
        $this->Rect(120, $startY, 80, 38, 'F');
        $this->SetFont('Arial', '', 9);

        $rows = [
            'PO no:'                  => $po->order_code,
            'Purchase Order Status:'  => ucfirst($po->status->value ?? $po->status),
            'Date:'                   => optional($po->ordered_at)->format('d.m.Y'),
            'Requested By:'           => $po->user->name ?? 'N/A',
            'Approved By:'            => $po->approved_by ?? 'N/A', 
            'Department:'             => $po->department ?? 'N/A',
        ];

        $y = $startY + 3;
        foreach ($rows as $label => $value) {
            $this->SetXY(124, $y);
            $this->Cell(45, 6, $label);
            $this->Cell(35, 6, (string) $value);
            $y += 6;
        }

        $this->SetY($startY + 42);
    }

    protected function shipToAndSupplier(PurchaseOrder $po): void
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Ship to', 0, 1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, $this->shipTo['name'], 0, 1);
        $this->SetFont('Arial', 'I', 9);
        $this->MultiCell(100, 5,
            "Attn: {$this->shipTo['attn']}\n{$this->shipTo['address']}\nPhone: {$this->shipTo['phone']}"
        );
        $this->Ln(4);

        $supplier = $po->supplier;
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Supplier', 0, 1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, $supplier->name, 0, 1);
        $this->SetFont('Arial', 'I', 9);
        $this->MultiCell(100, 5,
            ($supplier->address ?? '') . "\n" .
            'Phone: ' . ($supplier->phone ?? '-') . "\n" .
            'Email: ' . ($supplier->email ?? '-') . "\n" .
            'Terms: ' . ($supplier->terms ?? '-')
        );
    }

    protected function itemsTable(PurchaseOrder $po): void
    {
        $this->Ln(6);
        $this->SetFont('Arial', 'B', 9);
        $widths = [30, 60, 20, 20, 30, 30];
        $headers = ['Item #', 'Description', 'Qty', 'Unit', 'Unit Price', 'Total'];
        foreach ($headers as $i => $h) {
            $this->Cell($widths[$i], 8, $h, 'B');
        }
        $this->Ln();

        $this->SetFont('Arial', '', 9);
        foreach ($po->products as $item) {
            $lineTotal = $item->quantity * $item->unit_price;
            $this->Cell($widths[0], 8, $item->product->product_code ?? '-', 0);
            $this->Cell($widths[1], 8, $item->product->name ?? '-', 0);
            $this->Cell($widths[2], 8, (string) $item->quantity, 0);
            $this->Cell($widths[3], 8, $item->product->unit ?? '-', 0);
            $this->Cell($widths[4], 8, $this->money($item->unit_price), 0);
            $this->Cell($widths[5], 8, $this->money($lineTotal), 0);
            $this->Ln();
        }
    }

    protected function totals(PurchaseOrder $po): void
    {
        $subtotal = $po->products->sum(fn ($i) => $i->quantity * $i->unit_price);
        $total = $po->total_amount ?? $subtotal;

        $this->Ln(6);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(150, 6, 'Subtotal', 0, 0, 'R');
        $this->Cell(30, 6, $this->money($subtotal), 0, 1, 'R');

        $this->SetTextColor(0, 172, 193);
        $this->Cell(150, 8, 'Total', 0, 0, 'R');
        $this->Cell(30, 8, $this->money($total), 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    protected function money(float $amount): string
    {
        return iconv('UTF-8', 'windows-1252', number_format($amount, 2) . ' INR');
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