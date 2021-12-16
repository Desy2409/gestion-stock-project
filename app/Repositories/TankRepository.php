<?php

namespace App\Repositories;

use App\Models\Tank;

class TankRepository extends Repository
{
    public function tankReport($reference = false, $tank_registration = false, $provider = false, $compartment = false, $startDate = null, $endDate = null)
    {
        if (!$reference && !$tank_registration && !$provider && !$compartment && $startDate == null && $endDate == null) {
            $tanks = null;
        } else {
            $tanks = Tank::where('id', '!=', null);
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($tank_registration) {
                array_push($this->columns, 'tank_registration');
            }
            if ($provider) {
                array_push($this->columns, 'provider_id');
                $tanks->with('provider');
            }
            if ($compartment) {
                array_push($this->columns, 'compartment_id');
                $tanks->with('compartment');
            }
            if ($startDate && $endDate) {
                $tanks->whereBetween('created_at', [$startDate, $endDate]);
            }
            $tanks = $tanks->get($this->columns);
        }

        return $tanks;
    }
}
