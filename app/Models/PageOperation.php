<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageOperation extends Model
{

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }
}
