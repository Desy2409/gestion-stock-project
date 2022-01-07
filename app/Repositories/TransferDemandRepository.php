<?php

namespace App\Repositories;

use App\Models\TransferDemand;

class TransferDemandRepository extends Repository
{
    public function transferDemandReport($code = false, $requestReason = false, $dateOfDemand = false, $deliveryDeadline = false, $state = false, $transmitter = false, $receiver = false, $dateOfProcessing = false, $startDate = null, $endDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$dateOfDemand && !$requestReason && !$deliveryDeadline && !$state && !$transmitter && !$receiver && !$dateOfProcessing && $startDate == null && $endDate == null && $startProcessingDate==null && $endProcessingDate==null) {
            $transferDemands = TransferDemand::all();
        } else {
            $transferDemands = TransferDemand::where('id', '!=', null);

            // if ($transmitter) {
            //     array_push($this->columns, 'transmitter_id');
            //     $transferDemands->with('transmitter');
            // }
            // if ($receiver) {
            //     array_push($this->columns, 'receiver_id');
            //     $transferDemands->with('receiver');
            // }
            // if ($startDate && $endDate) {
            //     $transferDemands->whereBetween('created_at', [$startDate, $endDate]);
            // }
            // if ($startProcessingDate && $endProcessingDate) {
            //     $transferDemands->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            // }

        }

        return $transferDemands;
    }
}
