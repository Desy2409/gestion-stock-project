<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageOperation extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
