<?php

namespace App\Repositories;

use App\Models\TransferDemand;

class TransferDemandRepository extends Repository
{
    public function transferDemandReport($code = false, $requestReason = false, $dateOfDemand = false, $deliveryDeadline = false, $state = false, $transmitter = false, $receiver = false, $dateOfProcessing = false, $startDate = null, $endDate = null, $startProcessingDate = null, $endProcessingDate = null)
    {
        if (!$code && !$dateOfDemand && !$requestReason && !$deliveryDeadline && !$state && !$transmitter && !$receiver && !$dateOfProcessing && $startDate == null && $endDate == null && $startProcessingDate==null && $endProcessingDate==null) {
            $transferDemands = null;
        } else {
            $transferDemands = TransferDemand::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($requestReason) {
                array_push($this->columns, 'request_reason');
            }
            if ($dateOfDemand) {
                array_push($this->columns, 'date_of_demand');
            }
            if ($deliveryDeadline) {
                array_push($this->columns, 'delivery_deadline');
            }
            if ($state) {
                array_push($this->columns, 'state');
            }
            if ($transmitter) {
                array_push($this->columns, 'transmitter_id');
                $transferDemands->with('transmitter');
            }
            if ($receiver) {
                array_push($this->columns, 'receiver_id');
                $transferDemands->with('receiver');
            }
            if ($dateOfProcessing) {
                array_push($this->columns, 'date_of_processing');
            }
            if ($startDate && $endDate) {
                $transferDemands->whereBetween('created_at', [$startDate, $endDate]);
            }
            if ($startProcessingDate && $endProcessingDate) {
                $transferDemands->whereBetween('date_of_processing', [$startProcessingDate, $endProcessingDate]);
            }
            $transferDemands = $transferDemands->get($this->columns);
        }

        return $transferDemands;
    }
}
