<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDeliveryNote extends Model
{
    protected $fillable = [
        'reference',
        'delivery_note_date',
        'delivery_date',
        'total_amount',
        'observation'
    ];

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function productClientDeliveryNotes()
    {
        return $this->hasMany(ProductClientDeliveryNote::class);
    }
}
