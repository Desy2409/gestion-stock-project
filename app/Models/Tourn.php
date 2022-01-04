<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tourn extends Model
{
    protected $fillable = [
        'code',
        'reference',
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
}
