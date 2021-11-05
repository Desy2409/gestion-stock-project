<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compartment extends Model
{
    protected $fillable = [
        'reference',
        'number',
        'capacity'
    ];

    public function tanks()
    {
        return $this->hasMany(Tank::class);
    }
}
