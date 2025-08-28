<?php

namespace App\Http\Services;

use App\Models\RhAlteracaoMensal;
use Carbon\Carbon;

class RhAlteracaoMensalService
{
    public static function getAllRhAlteracoesMensais($nif, $request){
        return RhAlteracaoMensal::query()
                                ->where('empresa', $nif)
                                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                                    $q->whereBetween('data',[$request->start_date,$request->end_date]);
                                })
                                ->orderBy('data','asc')
                                ->get();
    }

    public static function storeRhAlteracaoMensal($request){
        $data['empresa'] = $request->empresa;
        $data['funcionario'] = $request->funcionario;
        $data['data'] = $request->data;
        $data['rubrica'] = $request->rubrica;
        $data['quantidade'] = $request->quantidade;
        $data['valor'] = $request->valor;
        $data['motivo'] = $request->motivo;
        $rhAlteracaoMensalSaved = RhAlteracaoMensal::Create($data);
        return $rhAlteracaoMensalSaved;
    }

    public static function getAllRhAlteracoesMensaisByFuncionarioId($nif, $funcionario_id, $request){
        $data = RhAlteracaoMensal::query()
                                ->where('empresa',$nif)
                                ->where('funcionario', $funcionario_id)
                                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                                    $q->whereBetween('data',[$request->start_date,$request->end_date]);
                                })
                                ->orderBy('data','asc')
                                ->get();  
        return $data;
    }

}