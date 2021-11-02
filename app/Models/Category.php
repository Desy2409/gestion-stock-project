<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'reference',
        'wording',
        'description'
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    // public static function boot()
    // {
    //     parent::boot();

    //     self::creating(function($model){

    //         $model->description = 'blabla';

    //     });
    // }

}
