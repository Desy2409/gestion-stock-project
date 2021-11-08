<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'reference',
        'wording',
        'description'
    ];

    public function tourns()
    {
        return $this->hasMany(Tourn::class);
    }
}
