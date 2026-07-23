<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 30px 40px 40px 40px;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #1a1a1a;
        font-size: 14px;
    }
    table {
        border-collapse: collapse;
    }
    .full-width {
        width: 100%;
    }

    /* Header */
    .title {
        color: #00acc1;
        font-size: 54px;
        font-weight: bold;
        margin: 0;
        padding: 0;
    }
    .logo-circle {
        width: 80px;
        height: 80px;
        background-color: #f5a623;
        border-radius: 50%;
        color: #ffffff;
        text-align: center;
        vertical-align: middle;
        font-weight: bold;
        font-size: 14px;
    }
    .logo-cell {
        text-align: right;
        vertical-align: top;
    }

    /* Divider */
    .divider-cell {
        border-bottom: 3px solid #00acc1;
        font-size: 1px;
        line-height: 1px;
        padding: 0;
    }

    /* Section headings */
    .section-title {
        font-weight: bold;
        font-size: 16px;
        padding-bottom: 3px;
    }
    .bold-line {
        font-weight: bold;
        font-size: 14px;
        padding-bottom: 2px;
        color: #333333;
    }
    .italic-block {
        font-style: italic;
        color: #333333;
        line-height: 20px;
    }

    /* PO meta box */
    .po-meta-box {
        background-color: #f2f2f2;
        padding: 14px 16px;
    }
    .po-meta-box td {
        padding: 3px 0;
        font-size: 14px;
    }
    .po-meta-label {
        color: #444444;
        width: 140px;
        font-size: 14px;
    }

    /* Comments box */
    .comments-note {
        color: #999999;
        font-size: 14px;
    }

    /* Items table */
    .items-table th {
        border-top: 2px solid #00acc1;
        border-bottom: 1px solid #cccccc;
        text-align: left;
        padding: 16px 8px;
        font-size: 14px;
    }
    .items-table td {
        padding: 16px 8px;
        border-bottom: 1px solid #eeeeee;
        font-size: 14px;
        text-align: left;
        color: #999999;
        white-space: nowrap;
    }
    .text-right {
        text-align: right;
        white-space: nowrap;
    }

    /* Totals */
    .totals-table td {
        padding: 4px 0;
        font-size: 14px;
    }
    .totals-label {
        text-align: right;
        font-weight: bold;
        padding-right: 20px;
        width: 85%;
    }
    .totals-value {
        text-align: right;
        width: 15%;
    }
    .total-row td {
        color: #00acc1;
        font-weight: bold;
        font-size: 13px;
        padding-top: 8px;
    }

    /* Footer bar */
    .footer-bar {
        position: fixed;
        bottom: -35px;
        left: -40px;
        right: -40px;
        height: 14px;
        background-color: #00acc1;
    }

    .spacer-sm { height: 10px; font-size: 1px; line-height: 1px; }
    .spacer-md { height: 20px; font-size: 1px; line-height: 1px; }
</style>
</head>
<body>

    <!-- Header: Title + Logo -->
    <table class="full-width" style="margin:32px 0px;">
        <tr>
            <td style="">
                <p class="title">Purchase Order</p>
            </td>
            <td class="logo-cell" style="width: 90px;">
                <table style="width: 65px; margin-left: auto;">
                    <tr>
                        <td class="logo-circle">Logo</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="full-width">
        <tr><td class="divider-cell">&nbsp;</td></tr>
    </table>

    <table class="full-width"><tr><td class="spacer-md">&nbsp;</td></tr></table>

    <!-- Company block + PO meta box -->
    <table class="full-width">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="section-title">{{ $company['name'] }}</div>
                <div class="italic-block">
                    {!! nl2br(e($company['address'])) !!}<br>
                    Phone: {{ $company['phone'] }}
                </div>

    <table class="full-width"><tr><td class="spacer-md">&nbsp;</td></tr></table>
    <table class="full-width"><tr><td class="spacer-md">&nbsp;</td></tr></table>

                 <!-- Ship to -->
    <table class="full-width">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="section-title">Ship to</div>
                <div class="bold-line">{{ $shipTo['name'] }}</div>
                <div class="italic-block">
                    Attn: {{ $shipTo['attn'] }}<br>
                    {!! nl2br(e($shipTo['address'])) !!}<br>
                    Phone: {{ $shipTo['phone'] }}
                </div>
            </td>
        </tr>
    </table>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="po-meta-box full-width">
                    <tr>
                        <td class="po-meta-label">PO no:</td>
                        <td>{{ $purchaseOrder->order_code }}</td>
                    </tr>
                    <tr>
                        <td class="po-meta-label">Purchase Order Status:</td>
                        <td>{{ ucfirst(is_object($purchaseOrder->status) ? $purchaseOrder->status->value : $purchaseOrder->status) }}</td>
                    </tr>
                    <tr>
                        <td class="po-meta-label">Date:</td>
                        <td>{{ optional($purchaseOrder->ordered_at)->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td class="po-meta-label">Requested By:</td>
                        <td>{{ $purchaseOrder->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="po-meta-label">Approved By:</td>
                        <td>{{ $purchaseOrder->approved_by ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="po-meta-label">Department:</td>
                        <td>{{ $purchaseOrder->department ?? 'N/A' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="full-width"><tr><td class="spacer-md">&nbsp;</td></tr></table>

   

    <table class="full-width"><tr><td class="spacer-md">&nbsp;</td></tr></table>

    <!-- Supplier + Terms of sale -->
    <table class="full-width">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="section-title">Supplier</div>
                <div class="bold-line">{{ $purchaseOrder->supplier->name }}</div>
                <div class="italic-block">
                    {{ $purchaseOrder->supplier->address ?? '' }}<br>
                    Phone: {{ $purchaseOrder->supplier->phone ?? '-' }}<br>
                    Email: {{ $purchaseOrder->supplier->email ?? '-' }}<br>
                    Terms: {{ $purchaseOrder->supplier->terms ?? '-' }}
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="section-title">Terms of sale and other comments</div>
                <div class="comments-note">Add any additional instructions or terms here.</div>
            </td>
        </tr>
    </table>

    <table class="full-width"><tr><td class="spacer-md">&nbsp;</td></tr></table>

    <!-- Items table -->
    <table class="full-width items-table" style="margin-top:10px">
        <thead>
            <tr>
                <th style="width: 15%;">Item #</th>
                <th style="width: 35%;">Description</th>
                <th class="text-right" style="width: 10%;">Qty</th>
                <th style="width: 10%;">Unit</th>
                <th class="text-right" style="width: 15%;">Unit Price</th>
                <th class="text-right" style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrder->products as $item)
                <tr>
                    <td>{{ $item->product->product_code ?? '-' }}</td>
                    <td>{{ $item->product->name ?? '-' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td>{{ $item->product->unit ?? '-' }}</td>
                    <td class="text-right">£ {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">£ {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $subtotal = $purchaseOrder->products->sum(fn($i) => $i->quantity * $i->unit_price);
        $tax = $purchaseOrder->tax ?? 0;
        $shipping = $purchaseOrder->shipping ?? 0;
        $other = $purchaseOrder->other ?? 0;
        $total = $purchaseOrder->total_amount ?? ($subtotal + $tax + $shipping + $other);
    @endphp

    <!-- Totals -->
    <table class="full-width totals-table" style="margin-top:16px;">
        <tr>
            <td class="totals-label">Subtotal</td>
            <td class="totals-value">£ {{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label">Tax</td>
            <td class="totals-value">£ {{ number_format($tax, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label">Shipping</td>
            <td class="totals-value">£ {{ number_format($shipping, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label">Other</td>
            <td class="totals-value">£ {{ number_format($other, 2) }}</td>
        </tr>
        <tr class="total-row" >
            <td class="totals-label" style="padding-top:24px;">Total</td>
            <td class="totals-value" style="padding-top:24px;">£ {{ number_format($total, 2) }}</td>
        </tr>
    </table>

    <div class="footer-bar">&nbsp;</div>

</body>
</html>