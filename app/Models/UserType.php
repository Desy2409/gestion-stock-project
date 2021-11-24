<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    protected $casts=[
        'roles'=>'array',
    ];
}
