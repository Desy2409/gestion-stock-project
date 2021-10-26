<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransferLine extends Model
{
    protected $fillable = [
        'quantity',
        'unit_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }
}
