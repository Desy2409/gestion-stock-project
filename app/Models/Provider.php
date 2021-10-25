<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'settings'
    ];

    // protected $casts = [
    //     'settings' => 'array'
    // ];

    // public function setMetaAttribute($value)
    // {
    //     $settings = [];

    //     foreach ($value as $array_item) {
    //         if (!is_null($array_item['key'])) {
    //             $settings[] = $array_item;
    //         }
    //     }

    //     $this->attributes['settings'] = json_encode($settings);
    // }

    public function person(){
        return $this->morphOne(Person::class, 'personable');
    }
}
