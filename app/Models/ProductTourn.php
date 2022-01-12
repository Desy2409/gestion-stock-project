<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTourn extends Model
{
    public function tourn()
    {
        return $this->belongsTo(Tourn::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }
}
