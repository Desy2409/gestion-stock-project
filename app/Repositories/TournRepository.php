<?php

namespace App\Repositories;

use App\Models\ProviderType;
use App\Models\Tourn;

class TournRepository extends Repository
{
    public function tournReport($code = false, $reference = false, $tank = false, $truck = false, $destination = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$reference && !$tank && !$truck && !$destination && $startDate == null && $endDate == null) {
            $tourns = null;
        } else {
            $tourns = Tourn::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($tank) {
                array_push($this->columns, 'tank_id');
                $tourns->with('tank');
            }
            if ($truck) {
                array_push($this->columns, 'truck_id');
                $tourns->with('truck');
            }
            if ($destination) {
                array_push($this->columns, 'destination_id');
                $tourns->with('destination');
            }
            if ($startDate && $endDate) {
                $tourns->whereBetween('created_at', [$startDate, $endDate]);
            }
            $tourns = $tourns->get($this->columns);
        }

        return $tourns;
    }
}
