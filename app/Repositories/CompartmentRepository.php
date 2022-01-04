<?php

namespace App\Repositories;

use App\Models\Compartment;

class CompartmentRepository extends Repository
{
    public function compartmentReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $compartments = null;
        } else {
            $compartments = Compartment::select($selectedDefaultFields)->where('id', '!=', null);

            // if ($startDate && $endDate) {
            //     $compartments->whereBetween('created_at', [$startDate, $endDate]);
            // }
            $compartments = $compartments->get($this->columns);
        }

        return $compartments;
    }
}
