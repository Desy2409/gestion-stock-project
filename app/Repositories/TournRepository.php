<?php

namespace App\Repositories;

use App\Models\ProviderType;
use App\Models\Tourn;

class TournRepository extends Repository
{
    public function tournReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $tourns = Tourn::all();
        } else {
            $tourns = Tourn::select($selectedDefaultFields)->where('id', '!=', null)->get();

            // if ($tank) {
            //     array_push($this->columns, 'tank_id');
            //     $tourns->with('tank');
            // }
            // if ($truck) {
            //     array_push($this->columns, 'truck_id');
            //     $tourns->with('truck');
            // }
            // if ($destination) {
            //     array_push($this->columns, 'destination_id');
            //     $tourns->with('destination');
            // }
            // if ($startDate && $endDate) {
            //     $tourns->whereBetween('created_at', [$startDate, $endDate]);
            // }

        }

        return $tourns;
    }
}
