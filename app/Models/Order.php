<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference',
        'order_date',
        'delivery_date',
        'total_amount',
        'observation'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sealePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function productOrders()
    {
        return $this->hasMany(ProductOrder::class);
    }
}
