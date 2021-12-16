<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'code',
        'date_of_transfer',
        'transfer_reason',
        'date_of_receipt'
    ];

    protected $appends =  ['transmitter','receiver'];

    public function getTransmitterAttribute(){
        return SalePoint::where('id',$this->transmitter_id)->first();
    }

    public function getReceiverAttribute(){
        return SalePoint::where('id',$this->receiver_id)->first();
    }
    
    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function transferDemand()
    {
        return $this->belongsTo(TransferDemand::class);
    }

    public function productsTransfersLines()
    {
        return $this->hasMany(ProductTransferLine::class);
    }
}
