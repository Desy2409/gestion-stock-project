<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\ProductClientDeliveryNote;
use App\Models\PurchaseOrder;

class PurchaseOrderRepository extends Repository
{
    public function purchaseOrderReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
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
        $columns = ['product_client_delivery_notes.id','product_client_delivery_notes.quantity','product_client_delivery_notes.product_id','product_client_delivery_notes.unity_id'];
        
        $productClientDeliveryNotes = ProductClientDeliveryNote::join('client_delivery_notes', 'client_delivery_notes.id', '=', 'product_client_delivery_notes.client_delivery_note_id')
            ->join('sales', 'sales.id', '=', 'client_delivery_notes.sale_id')->join('purchase_orders', 'purchase_orders.id', '=', 'sales.purchase_order_id')
            ->join('products', 'products.id', '=', 'product_client_delivery_notes.product_id')->with('product')->with('unity')//join('unities', 'unities.id', '=', 'product_client_delivery_notes.unity_id')
            ->where('purchase_orders.id', $purchaseOrder->id)->distinct()->get($columns);

        return $productClientDeliveryNotes;
    }

    public function purchaseOrderBasedOnClientDeliveryNote()
    {
        $purchaseOrdersColumns = ['purchase_orders.id','purchase_orders.reference','purchase_orders.purchase_date'];
        $purchaseOrders = PurchaseOrder::join('sales', 'sales.purchase_order_id', '=', 'purchase_orders.id')->join('client_delivery_notes','client_delivery_notes.sale_id','=','sales.id')
            ->where('purchase_orders.id', '!=', null)->distinct()->get($purchaseOrdersColumns);

        return $purchaseOrders;
    }
}
