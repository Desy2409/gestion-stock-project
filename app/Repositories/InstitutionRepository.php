<?php

namespace App\Repositories;

use App\Models\Institution;

class InstitutionRepository extends Repository
{
    public function institutionReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $institutions = null;
        } else {
            $institutions = Institution::select($selectedDefaultFields)->where('id', '!=', null)->get();

            // if ($startDate && $endDate) {
            //     $institutions->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $institutions;
    }
}
