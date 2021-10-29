<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'code',
        'date_of_transfer',
        'transfer_reason',
        'date_of_receipt'
    ];

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function productsTransfersLines()
    {
        return $this->hasMany(ProductTransferLine::class);
    }
}
