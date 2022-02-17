<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    protected $fillable = [
        'wording', 'description', 'authorization_type',
        'authorization_user', 'authorization_password',
        'authorization_token', 'authorization_prefix',
        'authorization_key', 'authorization_value'
    ];

    public function apiServiceResponses()
    {
        return $this->hasMany(ApiServiceResponse::class);
    }

    public function apiServiceHeaders()
    {
        return $this->hasMany(ApiServiceHeader::class);
    }
}
