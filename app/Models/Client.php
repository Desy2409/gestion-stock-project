<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'last_name',
        'first_name',
        'rccm_number',
        'cc_number',
        'social_reason',
        'address',
        'email',
        'bp',
        'phone',
    ];

    public function juridicPersonality()
    {
        return $this->belongsTo(JuridicPersonality::class);
    }
}
