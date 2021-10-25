<?php

namespace App\Http\Traits;

trait UtilityTrait {


    function formateNPosition($prefix, $suffixe, $length)
    {
        $valueString = $prefix;
        $valueLength = strlen($valueString . $suffixe);
        while ($valueLength < $length) {
            $valueString = $valueString . '0';
            $valueLength = strlen($valueString . $suffixe);
        }
        return $valueString . $suffixe;
    }


}
