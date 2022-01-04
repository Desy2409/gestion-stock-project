<?php

namespace App\Repositories;

use App\Models\Host;

class HostRepository extends Repository
{
    public function hostReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $hosts = null;
        } else {
            $hosts = Host::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $hosts->with('driver');
            // }
            // if ($startDate && $endDate) {
            //     $hosts->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $hosts;
    }
}
