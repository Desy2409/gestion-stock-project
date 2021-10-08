<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'rccm_number',
        'cc_number',
        'social_reason',
        'address',
        'email',
        'bp',
        'phone',
    ];
}
