<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRegister extends Model
{
    protected $fillable = [
        'code'
    ];

    public static $code='PR';
}
