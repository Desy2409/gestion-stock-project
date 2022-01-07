<?php

namespace App\Repositories;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;

class SubCategoryRepository extends Repository
{
    public function subCategoryReport($selectedDefaultFields, $selectedParentFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $subCategories = null;
        } else {
            // $subCategories = SubCategory::select($selectedDefaultFields)->where('id', '!=', null)->with('category')->get();

            // dd('test');

            $stringTest = "category:id,";

            // $stringTest .= implode(',', $selectedDefaultFields);
            $stringTest .=implode(',', $selectedParentFields);
            //dd($stringTest);
            // $stringTest .= ',' . implode(',', $selectedParentFields);

            // dd($stringTest);

            // $subCategories = SubCategory::select('categories.reference')->where('id', '!=', null)->get();
            
            
            $subCategories = SubCategory::with('category')->select('wording')->get();
            //$subCategories = SubCategory::with($stringTest)

            // $subCategories = SubCategory::select($selectedDefaultFields)->where('id', '!=', null)
            //     ->with(array($stringTest))->get();

            // if ($startDate && $endDate) {
            //     $subCategories->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $subCategories;
    }
}
