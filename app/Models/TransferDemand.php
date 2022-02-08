<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDemand extends Model
{
    public static $code = 'DT';

    protected $appends =  ['transmitter', 'receiver', 'transfer_demand_state'];

    public function getTransmitterAttribute()
    {
        return SalePoint::where('id', $this->transmitter_id)->first();
    }

    public function getReceiverAttribute()
    {
        return SalePoint::where('id', $this->receiver_id)->first();
    }

    public function getTransferDemandStateAttribute()
    {
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

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function productsTransfersDemandsLines()
    {
        // dd('test 2');
        return $this->hasMany(ProductTransferDemandLine::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }
}
