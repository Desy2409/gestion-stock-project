<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDeliveryNote extends Model
{
    protected $appends =  ['client_delivery_note_state'];

    public function getClientDeliveryNoteStateAttribute(){
        $value = "";
        switch ($this->state) {
            case 'P':
                $value = "En attente";
                break;

            case 'S':
                $value = "Validé(e)";
                break;

            case 'A':
                $value = "Annulé(e)";
                break;

            default:
                $value = "En attente";
                break;
        }
        return $value;
    }

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

    public function tourns()
    {
        return $this->hasMany(Tourn::class);
    }
}
