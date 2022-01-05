<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tourn extends Model
{

    protected $casts = [
        'client_delivery_notes' => 'array'
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function tank()
    {
        return $this->belongsTo(Tank::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function removalOrder()
    {
        return $this->belongsTo(RemovalOrder::class);
    }

    public function clientDeliveryNote()
    {
        return $this->belongsTo(ClientDeliveryNote::class);
    }

    public function state()
    {
        return ($this->state == 'C') ? "Tournée clôturée" : "";
    }
}
