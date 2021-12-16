<?php

namespace App\Repositories;

use App\Models\Purchase;

class PurchaseRepository extends Repository
{
    public function purchaseReport($code = false, $reference = false, $purchase_date = false, $delivery_date = false, $date_of_processing = false, $state = false, $total_amount = false, $tva = false, $amount_token = false, $discount = false, $amount_gross = false, $ht_amount = false, $order = false, $provider = false, $salePoint = false, $observation = false, $startPurchaseDate = null, $endPurchaseDate = null, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$reference && !$purchase_date && !$delivery_date && !$date_of_processing && !$total_amount && !$tva && !$amount_token && !$discount && !$amount_gross && !$ht_amount && !$state && !$order && !$provider && !$salePoint && !$observation && !$startPurchaseDate && !$endPurchaseDate && !$startDeliveryDate && !$endDeliveryDate && !$startProcessingDate && !$endProcessingDate) {
            $purchases = null;
        } else {
            $purchases = Purchase::where('id', '!=', null);
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
            if ($order) {
                array_push($this->columns, 'order_id');
                $purchases->with('order');
            }
            if ($provider) {
                array_push($this->columns, 'provider_id');
                $purchases->with('provider');
            }
            if ($salePoint) {
                array_push($this->columns, 'sale_point_id');
                $purchases->with('salePoint');
            }
            if ($observation) {
                array_push($this->columns, 'observation');
            }
            if ($startPurchaseDate && $endPurchaseDate) {
                $purchases->whereBetween('purchase_date', [$startPurchaseDate, $endPurchaseDate]);
            }
            if ($startDeliveryDate && $endDeliveryDate) {
                $purchases->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $purchases->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $purchases = $purchases->get($this->columns);
        }

        return $purchases;
    }
}
