<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrderProduct;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'product_code',
        'name',
        'key',
        'description',
        'sku',
        'unit',
        'price',
        'current_stock', 
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'price'               => 'float',
        'low_stock_threshold' => 'integer',
        'is_active'           => 'boolean',
    ];

    // Auto generate product_code and sku
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $latest = Product::latest('id')->first();
            $number = $latest ? ($latest->id + 1) : 1;
            $product->product_code = 'PROD-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            if (empty($product->sku)) {
                $product->sku = 'SKU-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Belongs to many suppliers
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier');
    }

    // Has many stock transactions
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Has many purchase order items
    public function purchaseOrderProducts()
    {
        return $this->hasMany(PurchaseOrderProduct::class);
    }

}