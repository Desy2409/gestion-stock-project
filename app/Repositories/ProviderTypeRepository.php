<?php

namespace App\Repositories;

use App\Models\ProviderType;

class ProviderTypeRepository extends Repository
{
    public function providerTypeReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $providerTypes = ProviderType::all();
        } else {
            $providerTypes = ProviderType::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($startDate && $endDate) {
            //     $providerTypes->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $providerTypes;
    }
}
