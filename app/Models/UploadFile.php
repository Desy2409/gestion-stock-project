<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    protected $fillable=[
        'code',
        'name',
        'personalized_name',
        'original_file_name',
        'path',
        'size',
        'extension',
    ];
}
