<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsChannelParam extends Model
{
    protected $fillable = ['url', 'user', 'password', 'sender','interval'];

    protected $casts = [
        'type' => 'array',
        'sms_header_type' => 'array'
    ];

    protected $attributes = [
        'type' => '{
            "simple_http" : "",
            "json_body_server" : "",
            "xml_body_server" : ""
        }',
        'sms_header_type' => '{
            "basic" : "",
            "bearer" : "",
            "none" : "",
            "api_key" : ""
        }'
    ];

    public function correspondanceChannel()
    {
        return $this->morphOne(CorrespondanceChannel::class, 'channel');
    }
}
