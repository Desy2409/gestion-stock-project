<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDeliveryNote extends Model
{
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }
}
