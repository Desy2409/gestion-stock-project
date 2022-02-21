<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'rccm_number',
        'cc_number',
        'social_reason',
        'email',
        'phone_number',
        'address',
        'bp',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    protected $attributes = [
        'settings' => '{
            "blocking_number_of_attempt": "",
            "principal_currency": "",
            "principal_unit_of_measure": "",
            "from_order_to_delivery": "",
            "password_complexity": {
                "minuscule": true,
                "majuscule": true,
                "special_characters": true,
                "min_length": "",
                "new_password_diffrent_from_old>": ""
            },
            "taxes" : "",
            "smtp": {
                "host" : "",
                "port" : "",
                "reception_protocol" : "",
                "from_name" : "",
                "username" : "",
                "password" : "",
                "from_address" : "",
                "time_out" : ""
            },
            "sms": {
                "url" : "",
                "user" : "",
                "password" : "",
                "sender" : "",
                "interval" : "",
                "type" : {
                    "simple_http" : "",
                    "json_body_server" : "",
                    "xml_body_server" : ""
                },
                "sms_header_type" : {
                    "basic" : "",
                    "bearer" : "",
                    "none" : "",
                    "api_key" : ""
                }
            }
        }'
    ];

    public function salesPoints()
    {
        return $this->hasMany(SalePoint::class);
    }

    public function deliveryPoints()
    {
        return $this->hasMany(DeliveryPoint::class);
    }
}
