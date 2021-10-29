<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'reference',
        'sale_date',
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

    public function productSales()
    {
        return $this->hasMany(ProductSale::class);
    }
}
