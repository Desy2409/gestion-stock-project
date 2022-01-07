<?php

namespace App\Repositories;

use App\Models\PhoneOperator;

class PhoneOperatorRepository extends Repository
{
    public function phoneOperatorReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $phoneOperators = PhoneOperator::all();
        } else {
            $phoneOperators = PhoneOperator::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $phoneOperators->with('driver');
            // }
            // if ($startDate && $endDate) {
            //     $phoneOperators->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $phoneOperators;
    }
}
