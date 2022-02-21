<?php

namespace App\Repositories;

use App\Models\RemovalOrder;
use App\Models\Unity;

class RemovalOrderRepository extends Repository
{

    public function removalOrderRepositoryReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $unities = RemovalOrder::all();
        } else {
            $unities = RemovalOrder::select($selectedDefaultFields)->where('id', '!=', null)->get();
            // if (in_array('start_date',$selectedDefaultFields)&&in_array('end_date',$selectedDefaultFields)) {
            //     $unities->whereBetween('created_at', [$startDate, $endDate]);
            // }
            // $unities = $unities->get($this->columns);
        }

        return $unities;
    }
}
