<?php

namespace App\Repositories;

use App\Models\ProviderType;

class ProviderTypeRepository extends Repository
{
    public function providerTypeReport($reference = false, $wording = false, $description = false, $type = false, $startDate = null, $endDate = null)
    {
        if (!$reference && !$wording && !$description &&!$type && $startDate == null && $endDate == null) {
            $providerTypes = null;
        } else {
            $providerTypes = ProviderType::where('id', '!=', null);
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($wording) {
                array_push($this->columns, 'wording');
            }
            if ($description) {
                array_push($this->columns, 'description');
            }
            if ($type) {
                array_push($this->columns, 'type');
            }
            if ($startDate && $endDate) {
                $providerTypes->whereBetween('created_at', [$startDate, $endDate]);
            }
            $providerTypes = $providerTypes->get($this->columns);
        }

        return $providerTypes;
    }
}
