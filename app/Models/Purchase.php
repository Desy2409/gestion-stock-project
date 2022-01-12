<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\This;

class Purchase extends Model
{
    protected $appends =  ['purchase_state'];

    public function getPurchaseStateAttribute(){
        $value = "";
        switch ($this->state) {
            case 'P':
                $value = "En attente";
                break;

            case 'S':
                $value = "Validé(e)";
                break;

            case 'A':
                $value = "Annulé(e)";
                break;

            default:
                $value = "En attente";
                break;
        }
        return $value;
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function deliveryNote()
    {
        return $this->hasOne(DeliveryNote::class);
    }

    public function productPurchases()
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    // public function verifyQuantity(Product $product)
    // {
    //     $deliveredQuantity = 0;
    //     $allProductDeliveryNotes = [];

    //     $productPurchase = ProductPurchase::where('product_id', $product->id)->first();
    //     $quantityToDeliver = $productPurchase->quantity;

    //     foreach ($this->deliveryNotes as $key => $deliveryNote) {
    //         $productDeliveryNotes = ProductDeliveryNote::where('delivery_note_id', $deliveryNote->id)->where('product_id', $product->id)->get();
    //         foreach ($allProductDeliveryNotes as $key => $productDeliveryNotes) {
    //             $deliveredQuantity += $productDeliveryNotes->quantity;
    //         }
    //     }

    //     if ($quantityToDeliver > $deliveredQuantity) {
    //         $remainingQuantity = $quantityToDeliver - $deliveredQuantity;
    //     }

    //     return $remainingQuantity;
    // }
}
