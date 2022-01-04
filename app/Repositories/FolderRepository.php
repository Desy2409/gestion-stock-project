<?php

namespace App\Repositories;

use App\Models\Folder;

class FolderRepository extends Repository
{
    public function folderReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $folders = null;
        } else {
            $folders = Folder::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            
            // if ($driver) {
            //     $folders->with('driver');
            // }
            // if ($startDate && $endDate) {
            //     $folders->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $folders;
    }
}
