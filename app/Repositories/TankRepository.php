<?php

namespace App\Repositories;

use App\Models\Tank;

class TankRepository extends Repository
{
    public function tankReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $tanks = Tank::all();
        } else {
            $tanks = Tank::select($selectedDefaultFields)->where('id', '!=', null);
            if (in_array('provider_id', $selectedDefaultFields)) {
                // dd('in_array');
                $tanks->with('provider');
            }
            if (in_array('compartment_id', $selectedDefaultFields)) {
                $tanks->with('compartment');
            }
            // if (in_array('start_date',$selectedDefaultFields)&&in_array('end_date',$selectedDefaultFields)) {
            //     $tanks->whereBetween('created_at', [$startDate, $endDate]);
            // }
            // $tanks = $tanks->get($this->columns);
        }

        return $tanks->get();
    }

    public function tanksOfSelectedTruck($id)
    {
        $tanks = Tank::join('tank_trucks', 'tank_trucks.tank_id', '=', 'tanks.id')
            ->join('trucks', 'trucks.id', '=', 'tank_trucks.truck_id')->where('trucks.id', $id)->get();

        return $tanks;
    }
}
