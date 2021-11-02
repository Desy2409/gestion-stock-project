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

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseCoupons()
    {
        return $this->hasMany(PurchaseCoupon::class);
    }

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function trucks()
    {
        return $this->hasMany(Truck::class);
    }
    
    public function goodToRemoves()
    {
        return $this->hasMany(GoodToRemove::class);
    }
}
