<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'actual_quantity',
        'theoretical_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }
}
