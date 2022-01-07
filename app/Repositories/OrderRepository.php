<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository extends Repository
{
    public function orderReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
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
}
