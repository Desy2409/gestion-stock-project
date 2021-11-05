<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    protected $fillable = [
        'reference',
        'tank_registration',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function tourns()
    {
        return $this->hasMany(Tourn::class);
    }

    public function compartment()
    {
        return $this->belongsTo(Compartment::class);
    }
}
