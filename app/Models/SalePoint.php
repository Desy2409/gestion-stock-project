<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePoint extends Model
{
    protected $fillable = [
        'rccm_number',
        'cc_number',
        'social_reason',
        'email',
        'phone_number',
        'address'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function transfersDemands()
    {
        return $this->hasMany(TransferDemand::class);
    }
}
