<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $appends =  ['sale_state'];

    public static $code = 'VT';

    public function getSaleStateAttribute(){
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function productSales()
    {
        return $this->hasMany(ProductSale::class);
    }

    public function clientDeliveryNotes()
    {
        return $this->hasMany(ClientDeliveryNote::class);
    }

    public function clientDeliveryNote()
    {
        return $this->hasOne(ClientDeliveryNote::class);
    }

    public function fileUploads()
    {
        return $this->hasMany(FileUpload::class);
    }
}
