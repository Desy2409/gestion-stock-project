<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository extends Repository
{
    public function orderReport($code = false, $reference = false, $order_date = false, $delivery_date = false, $date_of_processing = false, $total_amount = false, $state = false, $observation = false, $provider = false, $salePoint = false, $startOrderDate = null, $endOrderDate = null, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$reference && !$order_date && !$delivery_date && !$date_of_processing && !$total_amount && !$state && !$observation && !$provider && !$salePoint && !$startOrderDate && !$endOrderDate && !$startDeliveryDate && !$endDeliveryDate && !$startProcessingDate && !$endProcessingDate) {
            $orders = null;
        } else {
            $orders = Order::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($order_date) {
                array_push($this->columns, 'order_date');
            }
            if ($delivery_date) {
                array_push($this->columns, 'delivery_date');
            }
            if ($date_of_processing) {
                array_push($this->columns, 'date_of_processing');
            }
            if ($total_amount) {
                array_push($this->columns, 'total_amount');
            }
            if ($state) {
                array_push($this->columns, 'state');
            }
            if ($observation) {
                array_push($this->columns, 'observation');
            }
            if ($provider) {
                array_push($this->columns, 'provider_id');
                $orders->with('provider');
            }
            if ($salePoint) {
                array_push($this->columns, 'sale_point_id');
                $orders->with('salePoint');
            }
            if ($startOrderDate && $endOrderDate) {
                $orders->whereBetween('order_date', [$startOrderDate, $endOrderDate]);
            }
            if ($startDeliveryDate && $endDeliveryDate) {
                $orders->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $orders->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $orders = $orders->get($this->columns);
        }

        return $orders;
    }
}
