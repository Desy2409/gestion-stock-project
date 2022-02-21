<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    protected $fillable = [
        'reference', 'wording', 'description', 'authorization_type',
        'authorization_user', 'authorization_password',
        'authorization_token', 'authorization_prefix',
        'authorization_key', 'authorization_value', 'token_attribute',
        'body_type', 'body_content'
    ];

    public function apiService()
    {
        return $this->belongsTo(ApiService::class);
    }

    public function apiServices()
    {
        return $this->hasMany(ApiService::class);
    }

    public function apiServiceResponses()
    {
        return $this->hasMany(ApiServiceResponse::class);
    }

    public function apiServiceHeaders()
    {
        return $this->hasMany(ApiServiceHeader::class);
    }
}
