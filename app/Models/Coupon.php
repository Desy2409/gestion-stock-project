<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'reference',
        'purchase_date',
        'delivery_date',
        'amount_gross',
        'ht_amount',
        'discount',
        'amount_token',
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

    public function productCoupons()
    {
        return $this->hasMany(ProductCoupon::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }
}
