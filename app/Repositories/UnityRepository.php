<?php

namespace App\Repositories;

use App\Models\Unity;

class UnityRepository extends Repository
{

    public function unityReport($code = false, $wording = false, $description = false, $symbol = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$wording && !$description && !$symbol && $startDate == null && $endDate == null) {
            $unities = null;
        } else {
            $unities = Unity::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($wording) {
                array_push($this->columns, 'wording');
            }
            if ($description) {
                array_push($this->columns, 'description');
            }
            if ($symbol) {
                array_push($this->columns, 'symbol');
            }
            if ($startDate && $endDate) {
                $unities->whereBetween('created_at', [$startDate, $endDate]);
            }
            $unities = $unities->get($this->columns);
        }

        return $unities;
    }
}
