<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDeliveryNote extends Model
{
    protected $fillable = [
        'quantity',
        'unit_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
