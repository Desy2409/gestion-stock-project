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
        'max_size'
    ];

    protected $casts = [
        'authorized_files' => 'array'
    ];

    public function fileUploads()
    {
        return $this->hasMany(FileUpload::class);
    }
}
