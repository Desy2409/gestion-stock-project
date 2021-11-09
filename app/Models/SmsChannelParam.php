<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsChannelParam extends Model
{
    public function correspondanceChannel()
    {
        return $this->morphOne(CorrespondanceChannel::class, 'channel');
    }
}
