<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Institution;
use App\Models\SalePoint;

class Repository
{

    public $columns = [];

    public function reportIncludeCode($model, $selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $elements = null;
        } else {
            $elements = $model::select($selectedDefaultFields)->where('id', '!=', null)->get();
            // if ($startDate && $endDate) {
            //     $elements->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $elements;
    }

    public function reportIncludeReference($model, $selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $elements = null;
        } else {
            $elements = $model::select($selectedDefaultFields)->where('id', '!=', null)->get();
            // if ($startDate && $endDate) {
            //     $elements->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $elements;
    }

    public function registerReport($model, $selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $elements = null;
        } else {
            $elements = $model::select($selectedDefaultFields)->where('id', '!=', null)->get();
            // if ($startDate && $endDate) {
            //     $elements->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $elements;
    }
}
