<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;

class PurchaseOrderRepository extends Repository
{
    public function purchaseOrderReport($code = false, $reference = false, $purchase_date = false, $delivery_date = false, $date_of_processing = false, $total_amount = false, $state = false, $observation = false, $client = false, $salePoint = false, $startPurchaseDate = null, $endPurchaseDate = null, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$reference && !$purchase_date && !$delivery_date && !$date_of_processing && !$total_amount && !$state && !$observation&& !$client && !$salePoint && !$startPurchaseDate && !$endPurchaseDate && !$startDeliveryDate && !$endDeliveryDate && !$startProcessingDate && !$endProcessingDate) {
            $purchaseOrders = null;
        } else {
            $purchaseOrders = PurchaseOrder::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($purchase_date) {
                array_push($this->columns, 'purchase_date');
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
            if ($client) {
                array_push($this->columns, 'client_id');
                $purchaseOrders->with('client');
            }
            if ($salePoint) {
                array_push($this->columns, 'sale_point_id');
                $purchaseOrders->with('salePoint');
            }
            if ($startPurchaseDate && $endPurchaseDate) {
                $purchaseOrders->whereBetween('purchase_date', [$startPurchaseDate, $endPurchaseDate]);
            }
            if ($startDeliveryDate && $endDeliveryDate) {
                $purchaseOrders->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $purchaseOrders->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $purchaseOrders = $purchaseOrders->get($this->columns);
        }

        return $purchaseOrders;
    }
}
