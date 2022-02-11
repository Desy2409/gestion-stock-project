<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $fillable = ['password', 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
