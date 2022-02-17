<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailChannelParam extends Model
{
    protected $fillable = [
        'port',
        'username',
        'password',
        'encryption',
        'path',
        'channel',
        'from_adress',
        'from_name',
        'description',
        'is_active',
        'reception_protocol',
        'time_out'
    ];

    public function correspondenceChannel()
    {
        return $this->morphOne(CorrespondenceChannel::class, 'channel');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function host()
    {
        return $this->belongsTo(Host::class);
    }}
