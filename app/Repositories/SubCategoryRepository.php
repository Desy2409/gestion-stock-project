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


            $stringTest = "category:id,";

            // $stringTest .= implode(',', $selectedDefaultFields);
            $stringTest .=implode(',', $selectedParentFields);
            // $stringTest .= ',' . implode(',', $selectedParentFields);

            // dd($stringTest);

            // $subCategories = SubCategory::select('categories.reference')->where('id', '!=', null)->get(zzz);
            $subCategories = SubCategory::with($stringTest)->where('id', '!=', null)->get();


            // $subCategories = SubCategory::select($selectedDefaultFields)->where('id', '!=', null)
            //     ->with(array($stringTest))->get();

            // if ($startDate && $endDate) {
            //     $subCategories->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $subCategories;
    }
}
