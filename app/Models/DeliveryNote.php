<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $fillable = [
        'reference',
        'purchase_date',
        'delivery_date',
        'total_amount',
        'observation'
    ];

    public function purchaseCoupon()
    {
        return $this->belongsTo(PurchaseCoupon::class);
    }

    public function productDeliveryNotes()
    {
        return $this->hasMany(ProductDeliveryNote::class);
    }
}
