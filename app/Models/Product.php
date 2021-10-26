<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'wording',
        'description',
        'price',
        'unity',
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }

    public function productPurchaseOrders()
    {
        return $this->hasMany(ProductPurchaseOrder::class);
    }

    public function stockType()
    {
        return $this->belongsTo(StockType::class);
    }

    public function productsTransfersDemandsLines()
    {
        return $this->hasMany(ProductTransferDemandLine::class);
    }

    public function productsTransfersLines()
    {
        return $this->hasMany(ProductTransferLine::class);
    }
}