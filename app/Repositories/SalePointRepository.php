<?php

namespace App\Repositories;

use App\Models\SalePoint;

class SalePointRepository extends Repository
{
    public function salePointReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $salePoints = SalePoint::all();
        } else {
            $salePoints = SalePoint::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            // if ($institution) {
            //     array_push($this->columns, 'institution_id');
            //     $salePoints->with('institution');
            // }
            // if ($startDate && $endDate) {
            //     $salePoints->whereBetween('created_at', [$startDate, $endDate]);
            // }

        }

        return $salePoints;
    }
}
