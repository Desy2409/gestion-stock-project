<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDeliveryNote extends Model
{
    protected $fillable = [
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }

    public static function remainingQuantity()
    {
        // $purchase = $this->deliveryNote()->purchase;
        $purchase = parent::deliveryNote()->purchase;

        $quantityToDeliver = ProductPurchase::where('purchase_id', $purchase->id)->where('product_id', parent::product()->id)->first()->quantity;
        dd($quantityToDeliver);
        $deliveredQuantity = 0;
        $deliveredQuantity += ProductDeliveryNote::join('delivery_notes', 'delivery_notes.id', '=', 'product_delivery_notes.delivery_note_id')
            ->join('purchases', 'purchases.id', '=', 'delivery_notes.purchase_id')->where('purchases.id', $purchase->id)->sum('quantity');

        return ($quantityToDeliver > $deliveredQuantity) ? ($quantityToDeliver - $deliveredQuantity) : 0;
    }
}
