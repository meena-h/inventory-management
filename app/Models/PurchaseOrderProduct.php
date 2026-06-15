<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'float',
    ];

    // Belongs to a purchase order
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}