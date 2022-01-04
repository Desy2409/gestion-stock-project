<?php

namespace App\Repositories;

use App\Models\Unity;

class UnityRepository extends Repository
{

    public function unityReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $unities = null;
        } else {
            $unities = Unity::select($selectedDefaultFields)->where('id', '!=', null)->get();
            // if (in_array('start_date',$selectedDefaultFields)&&in_array('end_date',$selectedDefaultFields)) {
            //     $unities->whereBetween('created_at', [$startDate, $endDate]);
            // }
            // $unities = $unities->get($this->columns);
        }

        return $unities;
    }
}
