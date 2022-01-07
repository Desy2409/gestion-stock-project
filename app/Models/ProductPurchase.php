<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{

    protected $appends=['remaining_quantity'];

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

     public function getRemainingQuantityAttribute()
    {
        // $purchase = $this->deliveryNote()->purchase;
        // $purchase = $this->parent::purchase();
        // dd($this->purchase);

        $quantityToDeliver = ProductPurchase::where('purchase_id', $this->purchase->id)->first()->quantity;
        // dd($quantityToDeliver);
        $deliveredQuantity = 0;
        $deliveredQuantity += ProductDeliveryNote::join('delivery_notes', 'delivery_notes.id', '=', 'product_delivery_notes.delivery_note_id')
            ->join('purchases', 'purchases.id', '=', 'delivery_notes.purchase_id')->where('purchases.id', $this->purchase->id)->sum('quantity');

        return ($quantityToDeliver > $deliveredQuantity) ? ($quantityToDeliver - $deliveredQuantity) : 0;
        // return $quantityToDeliver;
    }
}
