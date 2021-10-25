<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'settings'
    ];

    public function person()
    {
        return $this->morphOne(Person::class, 'personable');
    }

    public function address()
    {
        return $this->hasMany(Address::class);
    }
}
