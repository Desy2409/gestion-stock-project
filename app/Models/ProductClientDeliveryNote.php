<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductClientDeliveryNote extends Model
{
    protected $fillable = [
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function clientDeliveryNote()
    {
        return $this->belongsTo(ClientDeliveryNote::class);
    }
    
    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }
}
