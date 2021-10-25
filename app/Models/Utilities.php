<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilities extends Model
{
    use HasFactory;

    function formateNPosition($prefix, $suffixe, $length)
    {
        $valueString = $prefix;
        $valueLength = strlen($valueString . $suffixe);
        while ($valueLength < $length) {
            $valueString = $valueString . '0';
            $valueLength = strlen($valueString . $suffixe);
        }
        return $valueString . $suffixe;
    }
}
