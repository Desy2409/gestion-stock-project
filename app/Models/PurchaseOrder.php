<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'purchase_date',
        'delivery_date',
        'total_amount',
        'observation'
    ];

    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function productOrders()
    {
        return $this->hasMany(ProductOrder::class);
    }
}
