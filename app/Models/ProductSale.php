<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    protected $appends = ['quantity_to_deliver', 'delivered_quantity', 'remaining_quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }

    public function getQuantityToDeliverAttribute()
    {
        return ProductSale::where('sale_id', $this->sale->id)->where('product_id', '=', $this->product->id)->first()->quantity;
    }

    public function getDeliveredQuantityAttribute()
    {
        $deliveredQuantity = 0;
        $deliveredQuantity = ProductClientDeliveryNote::join('client_delivery_notes', 'client_delivery_notes.id', '=', 'product_client_delivery_notes.client_delivery_note_id')
            ->join('sales', 'sales.id', '=', 'client_delivery_notes.sale_id')->where('sales.id', $this->sale->id)->where('product_id', '=', $this->product->id)->sum('quantity');
        return $deliveredQuantity;
    }

    public function getRemainingQuantityAttribute()
    {
        return ($this->getQuantityToDeliverAttribute() > $this->getDeliveredQuantityAttribute()) ? ($this->getQuantityToDeliverAttribute() - $this->getDeliveredQuantityAttribute()) : 0;
    }
}
