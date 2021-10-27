<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'rccm_number',
        'cc_number',
        'social_reason',
        'person_type'
    ];

    public function personable()
    {
        return $this->morphTo();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function address()
    {
        // $address = Address::latest('person_id', $this->id)->fisrt();
        // return $address;
        return $this->hasOne(Address::class)->latest();
    }
}
