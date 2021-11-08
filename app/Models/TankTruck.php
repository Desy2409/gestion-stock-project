<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankTruck extends Model
{
    protected $fillable = [
        'gauging_certificate',
        'validity_date'
    ];

    public function tank()
    {
        return $this->belongsTo(Tank::class);
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
