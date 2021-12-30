<?php

namespace App\Repositories;

use App\Models\Sale;

class SaleRepository extends Repository{
    public function saleReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $sales = null;
        } else {
            $sales = Sale::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($purchaseOrder) {
            //     array_push($this->columns, 'purchase_order_id');
            //     $sales->with('purchaseOrder');
            // }
            // if ($client) {
            //     array_push($this->columns, 'client_id');
            //     $sales->with('client');
            // }
            // if ($salePoint) {
            //     array_push($this->columns, 'sale_point_id');
            //     $sales->with('salePoint');
            // }
            // if ($startSaleDate && $endSaleDate) {
            //     $sales->whereBetween('sale_date', [$startSaleDate, $endSaleDate]);
            // }
            // if ($startDeliveryDate && $endDeliveryDate) {
            //     $sales->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            // }
            // if ($startProcessingDate && $endProcessingDate) {
            //     $sales->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            // }

        }

        return $sales;
    }
}