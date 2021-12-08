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
        // $formatAmonut = 0;
        switch ($locale) {
            case 'en':
                $formatAmonut = $amount; //str_replace($oldSeparator,$countrySeparator,$amount);
                break;

            case 'fr':
                $formatAmonut = str_replace('.', ',', $amount);
                break;

            case 'de':
                $formatAmonut = str_replace('.', ',', $amount);
                break;

            case 'it':
                $formatAmonut = str_replace('.', ',', $amount);
                break;

            case 'ja':
                $formatAmonut = str_replace('.', ',', $amount);
                break;

            case 'ko':
                $formatAmonut = $amount;
                break;

            case 'pt':
                $formatAmonut = $amount;
                break;

            case 'es':
                $formatAmonut = $amount;
                break;

            case 'zh_CN':
                $formatAmonut = $amount;
                break;

            case 'zh_TW':
                $formatAmonut = $amount;
                break;

            default:
                $formatAmonut = $amount;
                break;
        }

        return $formatAmonut;
    }
}
