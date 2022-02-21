<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovalOrder extends Model
{

    public static $code = 'BE';

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

    public function tourn()
    {
        return $this->hasOne(Tourn::class);
    }

    public function stockType()
    {
        return $this->belongsTo(StockType::class);
    }
}
