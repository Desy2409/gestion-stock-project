<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends Repository
{
    public function userReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $users = User::all();
        } else {
            $users = User::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $users->with('driver');
            // }
            // if ($startDate && $endDate) {
            //     $users->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $users;
    }
}
