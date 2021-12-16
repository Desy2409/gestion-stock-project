<?php

namespace App\Repositories;

use App\Models\ClientDeliveryNote;

class ClientDeliveryNoteRepository extends Repository
{
    public function clientDeliveryNoteReport($code = false, $reference = false, $delivery_date = false, $date_of_processing = false, $total_amount = false, $state = false, $observation = false, $sale = false, $tourn = false, $startDeliveryDate = null, $endDeliveryDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$reference && !$delivery_date && !$date_of_processing && !$total_amount && !$state && !$observation && !$sale && !$tourn && !$startDeliveryDate && !$endDeliveryDate && !$startProcessingDate && !$endProcessingDate) {
            $clientDeliveryNotes = null;
        } else {
            $clientDeliveryNotes = ClientDeliveryNote::where('id', '!=', null);
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
            if ($sale) {
                array_push($this->columns, 'sale_id');
                $clientDeliveryNotes->with('sale');
            }
            if ($tourn) {
                array_push($this->columns, 'tourn_id');
                $clientDeliveryNotes->with('tourn');
            }
            if ($startDeliveryDate && $endDeliveryDate) {
                $clientDeliveryNotes->whereBetween('delivery_date', [$endDeliveryDate, $endDeliveryDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $clientDeliveryNotes->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $clientDeliveryNotes = $clientDeliveryNotes->get($this->columns);
        }

        return $clientDeliveryNotes;
    }
}
