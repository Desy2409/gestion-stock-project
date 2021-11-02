<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderType extends Model
{
    protected $fillable = [
        'reference',
        'wording',
        'description',
        'type'
    ];

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
}
