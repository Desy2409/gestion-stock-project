<?php

namespace App\Repositories;

use App\Models\FileType;

class FileTypeRepository extends Repository
{
    public function fileTypeReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $fileTypes = FileType::all();
        } else {
            $fileTypes = FileType::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $fileTypes->with('driver');
            // }
            // if ($startDate && $endDate) {
            //     $fileTypes->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $fileTypes;
    }
}
