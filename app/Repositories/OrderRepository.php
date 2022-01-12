<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\ProductClientDeliveryNote;
use App\Models\ProductDeliveryNote;

class OrderRepository extends Repository
{
    public function orderReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $orders = Order::all();
        } else {
            $orders = Order::select($selectedDefaultFields)->where('id', '!=', null)->get();


            // if ($provider) {
            //     array_push($this->columns, 'provider_id');
            //     $orders->with('provider');
            // }
            // if ($salePoint) {
            //     array_push($this->columns, 'sale_point_id');
            //     $orders->with('salePoint');
            // }
            // if ($startOrderDate && $endOrderDate) {
            //     $orders->whereBetween('order_date', [$startOrderDate, $endOrderDate]);
            // }
            // if ($startDeliveryDate && $endDeliveryDate) {
            //     $orders->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            // }
            // if ($startProcessingDate && $endProcessingDate) {
            //     $orders->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            // }
        }

        return $orders;
    }


    public function orderDeliveredProducts(Order $order)
    {
        $productDeliveryNotes = ProductClientDeliveryNote::join('client_delivery_notes', 'client_delivery_notes.id', '=', 'product_client_delivery_notes.client_delivery_note_id')
            ->join('sales', 'sales.id', '=', 'client_delivery_notes.sale_id')->join('purchase_orders', 'purchase_orders.id', '=', 'sales.purchase_order_id')
            ->join('products', 'products.id', '=', 'product_client_delivery_notes.product_id')->where('purchase_orders.id', $order->id)->get();
        // $productDeliveryNotes = ProductDeliveryNote::join('delivery_notes', 'delivery_notes.id', '=', 'product_delivery_notes.delivery_note_id')
        // ->join('purchases', 'purchases.id', '=', 'delivery_notes.purchase_id')->join('orders', 'orders.id', '=', 'purchases.order_id')
        // ->join('products', 'products.id', '=', 'product_delivery_notes.product_id')->where('orders.id', $order->id)->get();

        return $productDeliveryNotes;
    }
}
