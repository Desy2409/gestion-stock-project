<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unity extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'symbol',
        'description'
    ];

    public function productPurchaseOrders()
    {
        return $this->hasMany(ProductPurchaseOrder::class);
    }

    public function productPurchases()
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function productDeliveryNotes()
    {
        return $this->hasMany(ProductDeliveryNote::class);
    }

    public function productOrders()
    {
        return $this->hasMany(ProductOrder::class);
    }

    public function productSales()
    {
        return $this->hasMany(ProductSale::class);
    }

    public function productClientDeliveryNotes()
    {
        return $this->hasMany(ProductClientDeliveryNote::class);
    }

    public function productTransferDemandLines()
    {
        return $this->hasMany(ProductTransferDemandLine::class);
    }

    public function productTransferLines()
    {
        return $this->hasMany(ProductTransferLine::class);
    }
}
