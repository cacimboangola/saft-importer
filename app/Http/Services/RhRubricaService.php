<?php

namespace App\Http\Services;

use App\Models\RhRubrica;
use Carbon\Carbon;

class RhRubricaService
{

    public static function getAllRhRubricas($nif){
        return RhRubrica::where('empresa',$nif)
        ->orderBy('rubrica','asc')->get();
    }

}