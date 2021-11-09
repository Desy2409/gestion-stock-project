<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFunction extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];
}
