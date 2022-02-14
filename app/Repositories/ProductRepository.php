<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends Repository
{
   public function productsOfCategory($id){
        $products = Product::join('sub_categories','sub_categories.id','=','products.sub_category_id')
        ->join('categories','categories.id','=','sub_categories.category_id')->where('category_id',$id)->get();

        return $products;
    }
}
