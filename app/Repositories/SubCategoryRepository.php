<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;

class SubCategoryRepository extends Repository
{
    public function subCategoryReport($selectedDefaultFields, $selectedParentFields)
    {
        // if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
        //     $subCategories = SubCategory::with('category')->orderBy('wording')->get();
        // } else {

        // dd($selectedDefaultFields,$selectedParentFields);
        $columns = [];
        if (!empty($selectedDefaultFields) && $selectedDefaultFields != null) {
            array_push($columns,'sub_categories.id as sub_id');
            foreach ($selectedDefaultFields as $key => $field) {
                $column = 'sub_categories.' . $field . ' as sub_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($selectedParentFields) && $selectedParentFields != null) {
            array_push($columns,'categories.id as cat_id');
            foreach ($selectedParentFields as $key => $field) {
                $column = 'categories.' . $field . ' as cat_' . $field;
                array_push($columns, $column);
            }
        }

        // dd($columns);


        // $columns = ['sub_categories.reference as sub_ref', 'categories.reference as cat_ref'];
        // $stringTest = "category:id,";

        // $stringTest .= implode(',', $selectedParentFields);
        // dd($stringTest);
        $subCategories = SubCategory::join('categories', 'categories.id', '=', 'sub_categories.category_id')->get($columns);
        // }

        return $subCategories;
    }
}
