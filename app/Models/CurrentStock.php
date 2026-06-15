<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentStock extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'current_stock',
        'updated_at',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'updated_at'    => 'datetime',
    ];

    // Belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}