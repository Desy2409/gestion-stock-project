<?php

namespace App\Http\Traits;

trait UtilityTrait
{

    function state($code)
    {
        $value = "";
        switch ($code) {
            case 'P':
                $value = "En attente";
                break;

            case 'S':
                $value = "Validé(e)";
                break;

            case 'A':
                $value = "Annulé(e)";
                break;

            case 'C':
                $value = "Clôturée";
                break;

            default:
                $value = "En attente";
                break;
        }

        return $value;
    }
}
