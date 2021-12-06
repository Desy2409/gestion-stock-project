<?php

namespace App\Http\Traits;

use NumberFormatter;

trait CurrencyTrait
{

    function formatCurrency($amount,$locale,$oldSeparator,$countrySeparator)
    {
        switch ($locale) {
            case 'value':
                $amount = str_replace($oldSeparator,$countrySeparator,$amount);
                break;
            
            case 'value':
                # code...
                break;
            
            default:
                # code...
                break;
        }
    }
}
