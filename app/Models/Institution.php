<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
    ];

    public function person(){
        return $this->morphOne(Person::class, 'person');
    }
}
