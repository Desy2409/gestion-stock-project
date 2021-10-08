<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'wording',
        'description',
        'price',
        'unity',
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
