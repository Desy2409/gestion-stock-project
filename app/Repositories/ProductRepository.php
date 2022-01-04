<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public $columns = [];

    public function productReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $products = null;
        } else {
            $products = Product::select($selectedDefaultFields)->where('id', '!=', null)->get();


            // if ($subCategory) {
            //     array_push($this->columns, 'sub_category_id');
            //     $products->with('subCategory');
            // }
            // if ($startDate && $endDate) {
            //     $products->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $products;
    }
}
