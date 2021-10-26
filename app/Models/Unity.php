<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unity extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function products()
    {
        return $this->hasMany(SubCategory::class);
    }
}
