<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDeliveryNote extends Model
{
    protected $fillable = [
        'code',
        'reference',
        'delivery_note_date',
        'delivery_date',
        'total_amount',
        'observation',
        'place_of_delivery'
    ];

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function productClientDeliveryNotes()
    {
        return $this->hasMany(ProductClientDeliveryNote::class);
    }

    public function tourn()
    {
        return $this->belongsTo(Tourn::class);
    }
}
