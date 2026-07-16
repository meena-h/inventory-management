<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'supplier_code',
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto generate supplier_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            $latest = Supplier::latest('id')->first();
            $number = $latest ? ($latest->id + 1) : 1;
            $supplier->supplier_code = 'SUP-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    // A supplier belongs to many products
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier');
    }
    
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}