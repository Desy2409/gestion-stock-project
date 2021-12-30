<?php

namespace App\Repositories;

use App\Models\SubCategory;

class SubCategoryRepository extends Repository
{
    public function subCategoryReport($selectedDefaultFields, $selectedParentFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $subCategories = null;
        } else {
            // $subCategories = SubCategory::select($selectedDefaultFields)->where('id', '!=', null)->with('category')->get();



            // $subCategories = SubCategory::select($selectedDefaultFields)->where('id', '!=', null)->with('category:'.implode(',',$selectedParentFields))->get();

//             $myArray = [];
// foreach ($selectedParentFields as $key => $value) {
//     array_push($myArray,'categories.'.$value);
// }

// dd($myArray);






            $stringTest = "category:id,";

            $stringTest .= implode(',', $selectedDefaultFields);
            $stringTest .= ',' . implode(',', $selectedParentFields);

            // dd($stringTest);

            $subCategories = SubCategory::select('category->reference')->where('id', '!=', null)->get();
            // $subCategories = SubCategory::with($myArray)->where('id', '!=', null)->get();


            // $subCategories = SubCategory::select($selectedDefaultFields)->where('id', '!=', null)
            //     ->with(array($stringTest))->get();




            // if (in_array('category_id',$selectedDefaultFields)) {
            //     // dd('in_array');
            //     $subCategories->with('category');
            // }

            // if ($category) {
            //     array_push($this->columns, 'category_id');
            //     $subCategories->with('category');
            // }
            // if ($startDate && $endDate) {
            //     $subCategories->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $subCategories;
    }
}
