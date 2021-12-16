<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDemand extends Model
{
    protected $fillable = [
        'code',
        'request_reason',
        'date_of_demand',
        'delivery_deadline',
        'date_of_processing',
        'state'
    ];

    protected $appends =  ['transmitter','receiver'];

    public function getTransmitterAttribute(){
        return SalePoint::where('id',$this->transmitter_id)->first();
    }

    public function getReceiverAttribute(){
        return SalePoint::where('id',$this->receiver_id)->first();
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
