<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $casts = ['delivery_points' => 'array'];

    public function person()
    {
        // return $this->morphOne('App\Models\Person','personable','personable_type','personable_code', 'code');
        return $this->morphOne(Person::class, 'personable');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function removalOrders()
    {
        return $this->hasMany(RemovalOrder::class);
    }
}
