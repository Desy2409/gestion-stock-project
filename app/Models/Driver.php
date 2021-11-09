<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    // public function emailChannelParams()
    // {
    //     return $this->hasMany(EmailChannelParams::class);
    // }

    public function hosts()
    {
        return $this->hasMany(Host::class);
    }
}
