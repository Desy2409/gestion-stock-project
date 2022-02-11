<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxe extends Model
{
    protected $fillable = ['reference', 'wording', 'value_type'];
}
