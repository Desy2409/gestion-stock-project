<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $fillable = [
        'code',
        'reference',
        'delivery_date',
        'total_amount',
        'observation',
        'place_of_delivery'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productDeliveryNotes()
    {
        return $this->hasMany(ProductDeliveryNote::class);
    }

    public function products(){
        
    }
}
