<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'code',
        'reference',
        'wording',
        'description',
        'price',
    ];

    protected $hidden = ['created_at'];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function price()
    {
        return $this->hasOne(ProductPricing::class)->latest();
    }

    public function productPurchaseOrders()
    {
        return $this->hasMany(ProductPurchaseOrder::class);
    }

    public function productsTransfersDemandsLines()
    {
        return $this->hasMany(ProductTransferDemandLine::class);
    }

    public function productsTransfersLines()
    {
        return $this->hasMany(ProductTransferLine::class);
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

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function totalQuantityPurchased()
    {
        $productPurchases = $this->productPurchases();
        $total = 0;
        foreach ($productPurchases as $key => $productPurchase) {
            $total += $productPurchase->quantity;
        }
        return $total;
    }

    public function totalQuantityDelivered()
    {
        $productDeliveryNotes = $this->productDeliveryNotes();
        $total = 0;
        foreach ($productDeliveryNotes as $key => $productDeliveryNote) {
            $total += $productDeliveryNote->quantity;
        }
        return $total;
    }

    public function remainsToDeliver(){

    }
}
