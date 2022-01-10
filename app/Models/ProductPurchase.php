<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{

    protected $appends = ['quantity_to_deliver', 'delivered_quantity', 'remaining_quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }

    public function getQuantityToDeliverAttribute()
    {
        return ProductPurchase::where('purchase_id', $this->purchase->id)->where('product_id', '=', $this->product->id)->first()->quantity;
    }

    public function getDeliveredQuantityAttribute()
    {
        $deliveredQuantity = 0;
        $deliveredQuantity = ProductDeliveryNote::join('delivery_notes', 'delivery_notes.id', '=', 'product_delivery_notes.delivery_note_id')
            ->join('purchases', 'purchases.id', '=', 'delivery_notes.purchase_id')->where('purchases.id', $this->purchase->id)->where('product_id', '=', $this->product->id)->sum('quantity');
        return $deliveredQuantity;
    }

    public function getRemainingQuantityAttribute()
    {
        return ($this->getQuantityToDeliverAttribute() > $this->getDeliveredQuantityAttribute()) ? ($this->getQuantityToDeliverAttribute() - $this->getDeliveredQuantityAttribute()) : 0;
    }
}
