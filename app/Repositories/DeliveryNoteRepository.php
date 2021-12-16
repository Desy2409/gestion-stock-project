<?php

namespace App\Repositories;

use App\Models\DeliveryNote;

class DeliveryNoteRepository extends Repository
{
    public function deliveryNoteReport($code = false, $reference = false, $delivery_date = false, $date_of_processing = false, $total_amount = false, $state = false, $observation = false, $purchase = false, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$reference && !$delivery_date && !$date_of_processing && !$total_amount && !$state && !$observation && !$purchase && !$startDeliveryDate && !$endDeliveryDate && !$startProcessingDate && !$endProcessingDate) {
            $deliveryNotes = null;
        } else {
            $deliveryNotes = DeliveryNote::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($reference) {
                array_push($this->columns, 'reference');
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
            if ($purchase) {
                array_push($this->columns, 'purchase_id');
                $deliveryNotes->with('purchase');
            }
            if ($startDeliveryDate && $endDeliveryDate) {
                $deliveryNotes->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $deliveryNotes->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $deliveryNotes = $deliveryNotes->get($this->columns);
        }

        return $deliveryNotes;
    }
}
