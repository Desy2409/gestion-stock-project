<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiServiceHeader extends Model
{
    protected $fillable = ['key', 'value'];

    public function apiService()
    {
        return $this->belongsTo(ApiService::class);
    }
}
