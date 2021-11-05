<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodToRemove extends Model
{
    protected $fillable = [
        'code',
        'reference',
        'voucher_date',
        'delivery_date_wished',
        'place_of_delivery',
        'voucher_type',
        'customs_regime',
        'storage_unit',
        'carrier',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function tourns()
    {
        return $this->hasMany(Tourn::class);
    }

    public function stockType()
    {
        return $this->belongsTo(StockType::class);
    }
}
