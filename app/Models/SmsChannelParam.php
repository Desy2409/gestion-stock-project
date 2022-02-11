<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsChannelParam extends Model
{
    protected $fillable = ['url', 'user', 'password', 'sender'];

    protected $casts = [
        'type' => 'array',
        'sms_header_type' => 'array'
    ];
    
    public function correspondanceChannel()
    {
        return $this->morphOne(CorrespondanceChannel::class, 'channel');
    }
}
