<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reference',
        'settings',
        'exemption_reference',
        'limit_date_exemption'
    ];

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
    
    public function goodToRemoves()
    {
        return $this->hasMany(GoodToRemove::class);
    }
}
