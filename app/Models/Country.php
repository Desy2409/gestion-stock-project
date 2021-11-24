<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'code',
        'alpha2',
        'alpha3',
        'name_en',
        'name_fr',
        'indicative',
    ];

    public function phoneOperators()
    {
        return $this->hasMany(PhoneOperator::class);
    }
}
