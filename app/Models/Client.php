<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'settings',
    ];

    public function person()
    {
        // return $this->morphOne('App\Models\Person','personable','personable_type','personable_code', 'code');
        return $this->morphOne(Person::class, 'personable');
    }

    public function address()
    {
        return $this->hasMany(Address::class);
    }
}
