<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'order_code',
        'status',
        'total_amount',
        'ordered_at',
        'received_at',
        'note',
    ];

    protected $casts = [
        'ordered_at'   => 'date',
        'received_at'  => 'date',
        'total_amount' => 'float',
    ];

    // Auto generate order_code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $latest = PurchaseOrder::latest('id')->first();
            $number = $latest ? ($latest->id + 1) : 1;
            $order->order_code = 'PO-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    // Belongs to a supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Belongs to a user (who created it)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Has many products
    public function products()
    {
        return $this->hasMany(PurchaseOrderProduct::class);
    }
}