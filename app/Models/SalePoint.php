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
        'address',
        'bp',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    protected $attributes = [
        'settings' => '{
            "location": {
                "latitude": "",
                "longitude": ""
            },
            "delivery_points": {},
            "order_purchase_order_validation_authorized_user": "",
            "transfert_deamnd_validation_authorized_user": ""
        }'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function transfersDemands()
    {
        return $this->hasMany(TransferDemand::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // public function clientDeliveryNotes()
    // {
    //     return $this->hasMany(ClientDeliveryNote::class);
    // }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function goodToRemoves()
    {
        return $this->hasMany(GoodToRemove::class);
    }
}
