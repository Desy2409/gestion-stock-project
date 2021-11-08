<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileType extends Model
{
    protected $fillable = [
        'code',
        'wording',
        'description',
        'authorized_files',
        'max_size'
    ];
}
