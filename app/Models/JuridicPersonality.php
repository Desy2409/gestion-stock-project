<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuridicPersonality extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
