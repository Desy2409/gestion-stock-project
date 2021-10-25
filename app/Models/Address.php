<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'email',
        'phone',
        'bp'
    ];

    public function person(){
        return $this->morphOne(Person::class, 'personable');
    }
}
