<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    protected $fillable = [
        'code',
        'provider',
        'url',
        'host_name'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function emailChannelParams()
    {
        return $this->hasMany(EmailChannelParams::class);
    }
}
