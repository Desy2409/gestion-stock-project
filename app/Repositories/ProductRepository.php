<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public $columns = [];

    public function productReport($code = false, $reference = false, $wording = false, $description = false, $price = false, $startDate = null, $endDate = null, $subCategory = false)
    {
        if (!$code && !$reference && !$wording && !$description && !$price && !$startDate && !$endDate && !$subCategory) {
            $products = null;
        } else {
            $products = Product::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
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
                $products->whereBetween('created_at', [$startDate, $endDate]);
            }
            if ($subCategory) {
                array_push($this->columns, 'sub_category_id');
                $products->with('subCategory');
            }
            if ($startDate && $endDate) {
                $products->whereBetween('created_at', [$startDate, $endDate]);
            }
            $products = $products->get($this->columns);
        }

        return $products;
    }
}
