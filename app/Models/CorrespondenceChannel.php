<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenceChannel extends Model
{
    protected $fillable=[
        'name',
        'description'
    ];

    public function channel()
    {
        return $this->morphTo();
    }
}
