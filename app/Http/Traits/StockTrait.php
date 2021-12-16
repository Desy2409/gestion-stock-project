<?php

namespace App\Http\Traits;

use App\Models\ClientDeliveryNote;
use App\Models\DeliveryNote;
use App\Models\Stock;

trait StockTrait
{

    function increment(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote && $deliveryNote->state == "S") {
            $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null;

            if (!empty($productDeliveryNotes) && sizeof($productDeliveryNotes) > 0) {
                foreach ($productDeliveryNotes as $key => $productDeliveryNote) {
                    $existingStock = Stock::where('product_id', $productDeliveryNote->product->id)->where('sale_point_id', $deliveryNote->purchase->salePoint->id)->first();
                    if ($existingStock) {
                        $existingStock->actual_quantity += $productDeliveryNote->quantity;
                        $existingStock->theoretical_quantity += $productDeliveryNote->quantity;
                        $existingStock->save();
                    } else {
                        $stock = new Stock();
                        $stock->actual_quantity = $productDeliveryNote->quantity;
                        $stock->theoretical_quantity = $productDeliveryNote->quantity;
                        $stock->sale_point_id = $deliveryNote->purchase->salePoint->id;
                        $stock->product_id = $productDeliveryNote->product->id;
                        $stock->save();
                    }
                }
            }
        }
    }

    function decrement(ClientDeliveryNote $clientDeliveryNote)
    {
        if ($clientDeliveryNote && $clientDeliveryNote->state == "S") {
            $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null;

            if (!empty($productClientDeliveryNotes) && sizeof($productClientDeliveryNotes) > 0) {
                foreach ($productClientDeliveryNotes as $key => $productClientDeliveryNote) {
                    $existingStock = Stock::where('product_id', $productClientDeliveryNote->product->id)->where('sale_point_id', $clientDeliveryNote->sale->salePoint->id)->first();
                    if ($existingStock && $existingStock->actual_quantity > 0) {
                        $existingStock->actual_quantity -= $productClientDeliveryNote->quantity;
                        $existingStock->theoretical_quantity -= $productClientDeliveryNote->quantity;
                        $existingStock->save();
                    }
                }
            }
        }
    }

    function decrementByRetunringDeliveryNote(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote && $deliveryNote->state == "S") {
            $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null;

            if (!empty($productDeliveryNotes) && sizeof($productDeliveryNotes) > 0) {
                foreach ($productDeliveryNotes as $key => $productDeliveryNote) {
                    $existingStock = Stock::where('product_id', $productDeliveryNote->product->id)->where('sale_point_id', $deliveryNote->purchase->salePoint->id)->first();
                    if ($existingStock && $existingStock->actual_quantity > 0) {
                        $existingStock->actual_quantity -= $productDeliveryNote->quantity;
                        $existingStock->theoretical_quantity -= $productDeliveryNote->quantity;
                        $existingStock->save();
                    }
                }
            }
        }
    }

    function decrementByRetunringClientDeliveryNote(ClientDeliveryNote $clientDeliveryNote)
    {
        if ($clientDeliveryNote && $clientDeliveryNote->state == "S") {
            $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null;

            if (!empty($productClientDeliveryNotes) && sizeof($productClientDeliveryNotes) > 0) {
                foreach ($productClientDeliveryNotes as $key => $productClientDeliveryNote) {
                    $existingStock = Stock::where('product_id', $productClientDeliveryNote->product->id)->where('sale_point_id', $clientDeliveryNote->sale->salePoint->id)->first();
                    if ($existingStock) {
                        $existingStock->actual_quantity += $productClientDeliveryNote->quantity;
                        $existingStock->theoretical_quantity += $productClientDeliveryNote->quantity;
                        $existingStock->save();
                    }
                }
            }
        }
    }
}
