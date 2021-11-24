<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StartNumber extends Model
{
    protected $fillable = [
        'number'
    ];

    public function phoneOperator()
    {
        return $this->belongsTo(PhoneOperator::class);
    }
}
