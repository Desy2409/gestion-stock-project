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
        'setting' => '{
            "order_purchase_order_validation_level": "",
            "blocking_number_of_attempt": "",
            "password_complexity": {
                "minuscule": true,
                "majuscule": true,
                "special_characters": true,
                "min_length": "",
                "old_password>": "",
                "new_password>": ""
            },
            "client_code_length": "",
            "provider_code_length": "",
            "goods_code_length": "",
            "order_purchase_order_number_recall_day": "",
            "offline_mode": "",
            "institution_type": "",
            "currency": "",
            "taxes": ""
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
