<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StockTransactionType;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'transaction_date',
        'type',
        'quantity',
        'note',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'integer',
        'type'             => StockTransactionType::class,
    ];

    // Belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Belongs to a user (who did the transaction)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}