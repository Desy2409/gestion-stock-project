<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiServiceResponse extends Model
{
    protected $fillable = ['response_type', 'response_content', 'response_state'];

    // protected $appends =['response_state'];

    // public function getResponseStateAttribute(){
    //     $value = "";
    //     switch ($this->state) {
    //         case 'P':
    //             $value = "En attente";
    //             break;

    //         case 'S':
    //             $value = "Succès";
    //             break;

    //         case 'R':
    //             $value = "Rejeté(e)";
    //             break;

    //         case 'F':
    //             $value = "Echoué(e)";
    //             break;

    //         case 'T':
    //             $value = "Temps dépassé";
    //             break;

    //         default:
    //             $value = "En attente";
    //             break;
    //     }
    //     return $value;
    // }

    public function apiService()
    {
        return $this->belongsTo(ApiService::class);
    }
}
