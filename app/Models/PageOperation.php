<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageOperation extends Model
{
    protected $fillable=['code','title','description','role'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }
}
