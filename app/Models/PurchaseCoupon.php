<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCoupon extends Model
{
    protected $fillable = [
        'reference',
        'purchase_date',
        'delivery_date',
        'total_amount',
        'observation'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function productPurchaseCoupons()
    {
        return $this->hasMany(ProductPurchaseCoupon::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }
}
