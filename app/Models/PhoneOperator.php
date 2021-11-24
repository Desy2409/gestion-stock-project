<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneOperator extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function startNumbers()
    {
        return $this->hasMany(StartNumber::class);
    }
}
