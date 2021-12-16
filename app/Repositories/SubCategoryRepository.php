<?php

namespace App\Repositories;

use App\Models\SubCategory;

class SubCategoryRepository extends Repository
{
    public function subCategoryReport($reference = false, $wording = false, $description = false, $startDate = null, $endDate = null, $category = false)
    {
        if (!$reference && !$wording && !$description && !$category && $startDate == null && $endDate == null) {
            $subCategories = null;
        } else {
            $subCategories = SubCategory::where('id', '!=', null);
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            if ($wording) {
                array_push($this->columns, 'wording');
            }
            if ($description) {
                array_push($this->columns, 'description');
            }
            if ($category) {
                array_push($this->columns, 'category_id');
                $subCategories->with('category');
            }
            if ($startDate && $endDate) {
                $subCategories->whereBetween('created_at', [$startDate, $endDate]);
            }
            $subCategories = $subCategories->get($this->columns);
        }

        return $subCategories;
    }
}
