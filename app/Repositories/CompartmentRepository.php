<?php

namespace App\Repositories;

use App\Models\Compartment;

class CompartmentRepository extends Repository
{
    public function compartmentReport($reference = false, $number = false, $capacity = false, $startDate = null, $endDate = null)
    {
        if (!$reference && !$number && !$capacity && !$startDate && !$endDate) {
            $compartments = null;
        } else {
            $compartments = Compartment::where('id', '!=', null);
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($number) {
                array_push($this->columns, 'number');
            }
            if ($capacity) {
                array_push($this->columns, 'capacity');
            }
            if ($startDate && $endDate) {
                $compartments->whereBetween('created_at', [$startDate, $endDate]);
            }
            $compartments = $compartments->get($this->columns);
        }

        return $compartments;
    }
}
