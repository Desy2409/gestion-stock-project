<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'settings',
    ];

    public function person(){
        return $this->morphOne('App\Models\Person','personable','personable_type','personable_code', 'code');
    }

     // public static function boot()
    // {
    //     parent::boot();

    //     self::creating(function($model){

    //         $model->code = '';

    //     });
    // }



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


}
