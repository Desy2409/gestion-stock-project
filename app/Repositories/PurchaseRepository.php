<?php

namespace App\Repositories;

use App\Models\Purchase;

class PurchaseRepository extends Repository
{
    public function purchaseReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $purchases = Purchase::all();
        } else {
            $purchases = Purchase::select($selectedDefaultFields)->where('id', '!=', null)->get();
            



            // if ($order) {
            //     array_push($this->columns, 'order_id');
            //     $purchases->with('order');
            // }
            // if ($provider) {
            //     array_push($this->columns, 'provider_id');
            //     $purchases->with('provider');
            // }
            // if ($salePoint) {
            //     array_push($this->columns, 'sale_point_id');
            //     $purchases->with('salePoint');
            // }
            // if ($observation) {
            //     array_push($this->columns, 'observation');
            // }
            // if ($startPurchaseDate && $endPurchaseDate) {
            //     $purchases->whereBetween('purchase_date', [$startPurchaseDate, $endPurchaseDate]);
            // }
            // if ($startDeliveryDate && $endDeliveryDate) {
            //     $purchases->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            // }
            // if ($startProcessingDate && $endProcessingDate) {
            //     $purchases->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            // }

        }

        return $purchases;
    }
}
