<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPricing extends Model
{
    protected $fillable = [
        'price',
        'date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
