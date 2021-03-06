<?php

namespace App\Repositories;

use App\Models\Truck;

class TruckRepository extends Repository
{

    public function truckReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $trucks = Truck::all();
        } else {
            $trucks = Truck::select($selectedDefaultFields)->where('id', '!=', null);
            if (in_array('provider_id',$selectedDefaultFields)) {
                // dd('in_array');
                $trucks->with('provider');
            }
            if (in_array('compartment_id',$selectedDefaultFields)) {
                $trucks->with('compartment');
            }
            // if (in_array('start_date',$selectedDefaultFields)&&in_array('end_date',$selectedDefaultFields)) {
            //     $trucks->whereBetween('created_at', [$startDate, $endDate]);
            // }
            // $trucks = $trucks->get($this->columns);
        }

        return $trucks->get();
    }


    public function truckReport_od($reference = false, $truck_registration = false, $provider = false, $compartment = false, $startDate = null, $endDate = null)
    {
        if (!$reference && !$truck_registration && !$provider &&!$compartment && $startDate == null && $endDate == null) {
            $trucks = null;
        } else {
            $trucks = Truck::where('id', '!=', null);
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($truck_registration) {
                array_push($this->columns, 'truck_registration');
            }
            if ($provider) {
                array_push($this->columns, 'provider_id');
                $trucks->with('provider');
            }
            if ($compartment) {
                array_push($this->columns, 'compartment_id');
                $trucks->with('compartment');
            }
            if ($startDate && $endDate) {
                $trucks->whereBetween('created_at', [$startDate, $endDate]);
            }
            $trucks = $trucks->get($this->columns);
        }

        return $trucks;
    }
}
