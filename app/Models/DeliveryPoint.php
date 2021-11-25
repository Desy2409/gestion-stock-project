<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPoint extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'latitude',
        'longitude',
        'description',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
