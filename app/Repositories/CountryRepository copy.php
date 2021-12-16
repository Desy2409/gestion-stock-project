<?php

namespace App\Repositories;

use App\Models\Country;
use App\Repositories\Repository;

class CountryRepository  extends Repository
{
    public function countryReport($code = false, $apha2 = false, $apha3 = false, $name_en = false, $name_fr = false, $indicative = false)
    {
        if (!$code && !$apha2 && !$apha3 && !$name_en && !$name_fr && !$indicative) {
            $countries = null;
        } else {
            $countries=Country::where('id','!=',null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($apha2) {
                array_push($this->columns, 'apha2');
            }
            if ($apha3) {
                array_push($this->columns, 'apha3');
            }
            if ($name_en) {
                array_push($this->columns, 'name_en');
            }
            if ($name_fr) {
                array_push($this->columns, 'name_fr');
            }
            if ($indicative) {
                array_push($this->columns, 'indicative');
            }
            $countries = Country::get($this->columns);
        }

        return $countries;
    }
}
