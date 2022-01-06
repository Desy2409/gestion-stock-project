<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $append =  ['state'];

    public function getStateAttribute(){
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

            case 'C':
                $value = "Clôturée";
                break;

            default:
                $value = "En attente";
                break;
        }
        return $value;
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function productOrders()
    {
        return $this->hasMany(ProductOrder::class);
    }

    public function purchases()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function salePoint()
    {
        return $this->belongsTo(SalePoint::class);
    }

    public function state()
    {
        return ($this->state == "P" ? "En attente" : "");
    }
}
