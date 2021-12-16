<?php

namespace App\Repositories;

use App\Models\Sale;

class SaleRepository extends Repository{
    public function saleReport($code = false, $reference = false, $sale_date = false, $date_of_processing = false, $state = false, $total_amount = false, $tva = false, $amount_token = false, $discount = false, $amount_gross = false, $ht_amount = false, $purchaseOrder = false, $client = false, $salePoint = false, $observation = false, $startSaleDate = null, $endSaleDate = null, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$reference && !$date_of_processing && !$total_amount && !$tva && !$amount_token && !$discount && !$amount_gross && !$ht_amount && !$state && !$purchaseOrder && !$client && !$salePoint && !$observation && !$startSaleDate && !$endSaleDate && !$startDeliveryDate && !$endDeliveryDate && !$startProcessingDate && !$endProcessingDate) {
            $sales = null;
        } else {
            $sales = Sale::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($sale_date) {
                array_push($this->columns, 'sale_date');
            }
            if ($date_of_processing) {
                array_push($this->columns, 'date_of_processing');
            }
            if ($total_amount) {
                array_push($this->columns, 'total_amount');
            }
            if ($tva) {
                array_push($this->columns, 'tva');
            }
            if ($amount_token) {
                array_push($this->columns, 'amount_token');
            }
            if ($discount) {
                array_push($this->columns, 'discount');
            }
            if ($amount_gross) {
                array_push($this->columns, 'amount_gross');
            }
            if ($ht_amount) {
                array_push($this->columns, 'ht_amount');
            }
            if ($state) {
                array_push($this->columns, 'state');
            }
            if ($purchaseOrder) {
                array_push($this->columns, 'purchase_order_id');
                $sales->with('purchaseOrder');
            }
            if ($client) {
                array_push($this->columns, 'client_id');
                $sales->with('client');
            }
            if ($salePoint) {
                array_push($this->columns, 'sale_point_id');
                $sales->with('salePoint');
            }
            if ($observation) {
                array_push($this->columns, 'observation');
            }
            if ($startSaleDate && $endSaleDate) {
                $sales->whereBetween('sale_date', [$startSaleDate, $endSaleDate]);
            }
            if ($startDeliveryDate && $endDeliveryDate) {
                $sales->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $sales->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $sales = $sales->get($this->columns);
        }

        return $sales;
    }
}