<?php

namespace App\Repositories;

use App\Models\UserType;

class UserTypeRepository extends Repository
{
    public function userTypeReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $userTypes = null;
        } else {
            $userTypes = UserType::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $userTypes->with('driver');
            // }
            // if ($startDate && $endDate) {
            //     $userTypes->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $userTypes;
    }
}
