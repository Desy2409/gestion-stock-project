<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockType extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
}
