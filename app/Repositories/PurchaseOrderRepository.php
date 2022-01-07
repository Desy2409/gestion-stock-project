<?php

namespace App\Repositories;

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
}
