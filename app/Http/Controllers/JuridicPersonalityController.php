<?php

namespace App\Http\Controllers;
use App\Models\JuridicPersonality;

use Illuminate\Http\Request;

class JuridicPersonalityController extends Controller
{
    public function index()
    {
        $juridic_personalities = JuridicPersonality::orderBy('wording')->get();
        return $juridic_personalities;
    }
}
