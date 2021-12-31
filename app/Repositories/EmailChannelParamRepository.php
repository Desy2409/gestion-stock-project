<?php

namespace App\Repositories;

use App\Models\EmailChannelParam;

class EmailChannelParamRepository extends Repository
{
    public function emailChannelParamReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $emailChannelParams = null;
        } else {
            $emailChannelParams = EmailChannelParam::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $emailChannelParams->with('driver');
            // }
            
            // if ($host) {
            //     $emailChannelParams->with('host');
            // }
            // if ($startDate && $endDate) {
            //     $emailChannelParams->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $emailChannelParams;
    }
}
