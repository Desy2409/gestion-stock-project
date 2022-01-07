<?php

namespace App\Repositories;

use App\Models\DeliveryPoint;

class DeliveryPointRepository extends Repository
{
    public function deliveryPointReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $deliveryPoints = DeliveryPoint::all();
        } else {
            $deliveryPoints = DeliveryPoint::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($institution) {
            //     $deliveryPoints->with('institution');
            // }
            // if ($startDate && $endDate) {
            //     $deliveryPoints->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $deliveryPoints;
    }
}
