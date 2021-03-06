<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function pageOperations()
    {
        return $this->hasMany(PageOperation::class);
    }
}
