<?php

namespace App\Repositories;

use App\Models\Transfer;

class TransferRepository extends Repository
{
    public function transferReport($code = false, $dateOfTransfer = false, $transferReason = false, $dateOfReceipt = false, $transmitter = false, $receiver = false, $transferDemand = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$dateOfTransfer && !$transferReason && !$dateOfReceipt && !$transmitter && !$receiver && !$transferDemand && $startDate == null && $endDate == null) {
            $transfers = null;
        } else {
            $transfers = Transfer::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($dateOfTransfer) {
                array_push($this->columns, 'date_of_transfer');
            }
            if ($transferReason) {
                array_push($this->columns, 'transfer_reason');
            }
            if ($dateOfReceipt) {
                array_push($this->columns, 'date_of_receipt');
            }
            if ($transmitter) {
                array_push($this->columns, 'transmitter_id');
                $transfers->with('transmitter');
            }
            if ($receiver) {
                array_push($this->columns, 'receiver_id');
                $transfers->with('receiver');
            }
            if ($transferDemand) {
                array_push($this->columns, 'transfer_demand_id');
                $transfers->with('transferDemand');
            }
            if ($startDate && $endDate) {
                $transfers->whereBetween('created_at', [$startDate, $endDate]);
            }
            $transfers = $transfers->get($this->columns);
        }

        return $transfers;
    }
}
