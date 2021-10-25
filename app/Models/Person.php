<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'lastName',
        'firstName',
        'rccmNumber',
        'ccNumber',
        'socialReason',
        'personType'
    ];

    public function personable()
    {
        return $this->morphTo();
    }
}
