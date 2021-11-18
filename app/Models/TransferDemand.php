<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDemand extends Model
{
    protected $fillable = [
        'code',
        'request_reason',
        'date_of_demand',
        'delivery_deadline',
        'date_of_processing',
        'state'
    ];

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function productsTransfersDemandsLines()
    {
        return $this->hasMany(ProductTransferDemandLine::class);
    }
}
