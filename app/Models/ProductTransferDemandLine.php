<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransferDemandLine extends Model
{
    protected $fillable = [
        'quantity',
        'unit_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transferDemand()
    {
        return $this->belongsTo(TransferDemand::class);
    }

    public function unity(){
        return $this->belongsTo(Unity::class);
    }
}
