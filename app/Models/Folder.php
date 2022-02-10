<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'affiliation',
        'path',
    ];

    public function children()
    {
        return $this->hasMany(Folder::class);
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class);
    }

    public function fileUploads()
    {
        return $this->hasMany(FileUpload::class);
    }
}
