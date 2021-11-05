<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPurchaseCoupon extends Model
{
    protected $fillable = [
        'quantity',
        'unit_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseCoupon()
    {
        return $this->belongsTo(PurchaseCoupon::class);
    }
    
    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }
}
