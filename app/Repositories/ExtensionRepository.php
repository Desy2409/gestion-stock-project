<?php

namespace App\Repositories;

use App\Models\Extension;

class ExtensionRepository extends Repository
{
    public function extensionReport($selectedDefaultFields)
    {
        if (empty($selectedDefaultFields)||sizeof($selectedDefaultFields)==0) {
            $extensions = null;
        } else {
            $extensions = Extension::select($selectedDefaultFields)->where('id', '!=', null)->get();
            
            // if ($startDate && $endDate) {
            //     $extensions->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $extensions;
    }
}
