<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Institution;
use App\Models\SalePoint;

class Repository
{

    public $columns = [];

    public function reportIncludeCode($model, $code = false, $wording = false, $description = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$wording && !$description && $startDate == null && $endDate == null) {
            $elements = null;
        } else {
            $elements = $model::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($wording) {
                array_push($this->columns, 'wording');
            }
            if ($description) {
                array_push($this->columns, 'description');
            }
            if ($startDate && $endDate) {
                $elements->whereBetween('created_at', [$startDate, $endDate]);
            }
            $elements = $elements->get($this->columns);
        }

        return $elements;
    }

    public function reportIncludeReference($model, $reference = false, $wording = false, $description = false, $startDate = null, $endDate = null)
    {
        if (!$reference && !$wording && !$description && $startDate == null && $endDate == null) {
            $elements = null;
        } else {
            $elements = $model::where('id', '!=', null);
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($wording) {
                array_push($this->columns, 'wording');
            }
            if ($description) {
                array_push($this->columns, 'description');
            }
            if ($startDate && $endDate) {
                $elements->whereBetween('created_at', [$startDate, $endDate]);
            }
            $elements = $elements->get($this->columns);
        }

        return $elements;
    }

    public function registerReport($model, $code = false, $startDate = null, $endDate = null)
    {
        if (!$code) {
            $elements = null;
        } else {
            $elements = $model::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($startDate && $endDate) {
                $elements->whereBetween('created_at', [$startDate, $endDate]);
            }
            $elements = $elements->get($this->columns);
        }

        return $elements;
    }
}
