<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'code',
        'reference',
        'order_date',
        'delivery_date',
        'total_amount',
        'observation',
        'state',
        'date_of_processing',
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
