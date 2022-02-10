<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = [
        'mime',
        'original_filename',
        'filename',
        'link',
        'personalized_filename',
        'size',
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function filetype()
    {
        return $this->belongsTo(FileType::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function purchaseOrder(){
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function sale(){
        return $this->belongsTo(Sale::class);
    }

    public function clientDeliveryNote()
    {
        return $this->belongsTo(ClientDeliveryNote::class);
    }
}
