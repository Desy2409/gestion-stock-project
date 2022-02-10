<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $appends =  ['delivery_note_state'];

    public static $code = 'BL';

    public function getDeliveryNoteStateAttribute(){
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

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productDeliveryNotes()
    {
        return $this->hasMany(ProductDeliveryNote::class);
    }

    public function fileUploads()
    {
        return $this->hasMany(FileUpload::class);
    }
    
    public function fileUpload(){
        return $this->morphOne(FileUpload::class, 'fileable');
    }
}
