<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

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

    public function productPurchaseOrders()
    {
        return $this->hasMany(ProductPurchaseOrder::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }
}
