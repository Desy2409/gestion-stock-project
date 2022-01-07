<?php

namespace App\Repositories;

use App\Models\Transfer;

class TransferRepository extends Repository
{
    public function transferReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $transfers = Transfer::all();
        } else {
            $transfers = Transfer::select($selectedDefaultFields)->where('id', '!=', null)->get();


            // if ($transmitter) {
            //     array_push($this->columns, 'transmitter_id');
            //     $transfers->with('transmitter');
            // }
            // if ($receiver) {
            //     array_push($this->columns, 'receiver_id');
            //     $transfers->with('receiver');
            // }
            // if ($transferDemand) {
            //     array_push($this->columns, 'transfer_demand_id');
            //     $transfers->with('transferDemand');
            // }
            // if ($startDate && $endDate) {
            //     $transfers->whereBetween('created_at', [$startDate, $endDate]);
            // }

        }

        return $transfers;
    }
}
