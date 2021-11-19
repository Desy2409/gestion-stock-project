<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'settings'
    ];

    public function person()
    {
        return $this->morphOne(Person::class, 'personable');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function trucks()
    {
        return $this->hasMany(Truck::class);
    }

    public function tanks()
    {
        return $this->hasMany(Tank::class);
    }

    public function goodToRemoves()
    {
        return $this->hasMany(GoodToRemove::class);
    }
}
