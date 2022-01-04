<?php

namespace App\Repositories;

use App\Models\DeliveryNote;

class DeliveryNoteRepository extends Repository
{
    // public function deliveryNoteReport($code = false, $reference = false, $delivery_date = false, $date_of_processing = false, $total_amount = false, $state = false, $observation = false, $purchase = false, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    public function deliveryNoteReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $deliveryNotes = null;
        } else {
            $deliveryNotes = DeliveryNote::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            // if ($purchase) {
            //     array_push($this->columns, 'purchase_id');
            //     $deliveryNotes->with('purchase');
            // }

            // if ($startDeliveryDate && $endDeliveryDate) {
            //     $deliveryNotes->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            // }
            // if ($startProcessingDate && $endProcessingDate) {
            //     $deliveryNotes->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            // }
        }

        return $deliveryNotes;
    }
}
