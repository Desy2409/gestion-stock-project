<?php

namespace App\Repositories;

use App\Models\ProductClientDeliveryNote;
use App\Models\PurchaseOrder;

class PurchaseOrderRepository extends Repository
{
    public function purchaseOrderReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $purchaseOrders = PurchaseOrder::all();
        } else {
            $purchaseOrders = PurchaseOrder::select($selectedDefaultFields)->where('id', '!=', null)->get();
            

            // if ($client) {
            //     array_push($this->columns, 'client_id');
            //     $purchaseOrders->with('client');
            // }
            // if ($salePoint) {
            //     array_push($this->columns, 'sale_point_id');
            //     $purchaseOrders->with('salePoint');
            // }
            // if ($startPurchaseDate && $endPurchaseDate) {
            //     $purchaseOrders->whereBetween('purchase_date', [$startPurchaseDate, $endPurchaseDate]);
            // }
            // if ($startDeliveryDate && $endDeliveryDate) {
            //     $purchaseOrders->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            // }
            // if ($startProcessingDate && $endProcessingDate) {
            //     $purchaseOrders->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            // }
        }

        return $purchaseOrders;
    }

    public function purchaseOrderDeliveredProducts(PurchaseOrder $purchaseOrder)
    {
        $productClientDeliveryNotes = ProductClientDeliveryNote::join('client_delivery_notes', 'client_delivery_notes.id', '=', 'product_client_delivery_notes.client_delivery_note_id')
            ->join('sales', 'sales.id', '=', 'client_delivery_notes.sale_id')->join('purchase_orders', 'purchase_orders.id', '=', 'sales.purchase_order_id')
            ->join('products', 'products.id', '=', 'product_client_delivery_notes.product_id')->where('purchase_orders.id', $purchaseOrder->id)->get();
        // $productDeliveryNotes = ProductDeliveryNote::join('delivery_notes', 'delivery_notes.id', '=', 'product_delivery_notes.delivery_note_id')
        // ->join('purchases', 'purchases.id', '=', 'delivery_notes.purchase_id')->join('orders', 'orders.id', '=', 'purchases.order_id')
        // ->join('products', 'products.id', '=', 'product_delivery_notes.product_id')->where('orders.id', $order->id)->get();

        return $productClientDeliveryNotes;
    }
}
