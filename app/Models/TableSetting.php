<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSetting extends Model
{
   protected $fillable=['table_name','code_min_length','validation_number','validation_reminder'];
}
