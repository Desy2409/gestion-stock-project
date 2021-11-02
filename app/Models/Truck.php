<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    protected $fillable = [
        'reference',
        'truck_registration',
        'tank_registration',
        'number_of_compartments',
        'capacity'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
