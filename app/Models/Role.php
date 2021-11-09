<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description'
    ];

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function pageOperation()
    {
        return $this->belongsTo(PageOperation::class);
    }
}
