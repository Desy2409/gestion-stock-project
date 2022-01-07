<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{

    protected $fillable=['code','title','description','operations'];

    protected $casts = [
        'operations' => 'array',
    ];

    public function pageOperations()
    {
        return $this->hasMany(PageOperation::class);
    }
}
