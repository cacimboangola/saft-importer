<?php

namespace App\Http\Services;

use App\Models\RhFuncionario;
use Carbon\Carbon;

class RhFuncionarioService
{
    public static function getAllRhFuncionarios($nif){
        return RhFuncionario::where('empresa',$nif)
        ->orderBy('nome','asc')->get();
    }
}