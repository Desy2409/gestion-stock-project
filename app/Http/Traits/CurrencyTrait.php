<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\App;
use NumberFormatter;

trait CurrencyTrait
{
    // private $locale;

    function formatCurrency($amount)
    {
        $locale = App::getLocale();
        
        switch ($locale) {
            case 'en':
                // $amount = str_replace($oldSeparator,$countrySeparator,$amount);
                break;

            case 'fr':
                # code...
                break;

            case 'de':
                # code...
                break;

            case 'it':
                # code...
                break;

            case 'ja':
                # code...
                break;

            case 'ko':
                # code...
                break;

            case 'pt':
                # code...
                break;

            case 'es':
                # code...
                break;

            case 'zh_CN':
                # code...
                break;

            case 'zh_TW':
                # code...
                break;

            default:
                # code...
                break;
        }
    }
}
