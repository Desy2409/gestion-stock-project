<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable=[
        'rccm_number',
        'cc_number',
        'social_reason',
        'email',
        'phone_number',
        'address'
    ];
}
