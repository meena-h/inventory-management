<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto generate category_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $latest = Category::latest('id')->first();
            $number = $latest ? ($latest->id + 1) : 1;
            $category->category_code = 'CAT-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    // A category has many products
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}