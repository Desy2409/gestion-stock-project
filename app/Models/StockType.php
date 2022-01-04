<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockType extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function removalOrders()
    {
        return $this->hasMany(RemovalOrder::class);
    }
}
