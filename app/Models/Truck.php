<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    protected $fillable = [
        'reference',
        'truck_registration',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function tourns()
    {
        return $this->hasMany(Tourn::class);
    }

    public function tankTrucks()
    {
        return $this->hasMany(TankTruck::class);
    }
}
