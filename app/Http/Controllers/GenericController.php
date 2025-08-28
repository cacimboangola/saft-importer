<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocResource;
use App\Http\Services\DocEmpresaService;
use App\Http\Services\DocEntidadeService;
use App\Http\Services\DocLinhaService;
use App\Http\Services\DocService;
use App\Http\Services\Util;
use App\Models\DocEmpresa;
use App\Models\DocEntidade;
use App\Models\DocLinha;
use App\Models\Docs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GenericController extends Controller
{
    //

    public function storeAllDataInDataBase(Request $request){
            DB::transaction(function () {
                $doc = DocService::insertDoc($request);
                $docLinha = DocLinhaService::insertDocLinha($request);
                $docEntidade = DocEntidadeService::insertDocEntidade($request);
                $docEmpresa = DocEmpresaService::insertDoc($request);
            });
                
        return response()->json([], 201);
    }
    public function getAllDataInDataBase(){ 
            $docs = DocService::getAllDocs();
            $docLinhas = DocLinhaService::getAllDocLinhas();
            $docEntidades = DocEntidadeService::getAllDocEntidades();
            $docEmpresas = DocEmpresaService::getAllDocEmpresas();
            return response()->json(['doc'=> $docs, 'doc_linha'=> $docLinhas, 
                                'doc_entidade'=>$docEntidades, 'doc_empresas'=>$docEmpresas], 
                                200);
    }
    public function uploadSaft(Request $request)
    {
        
        $file_name = Util::saftUpload($request);
        Util::readXMLAndReturnAnArray($file_name);
        Session::flash('success', 'Importação dos documentos concluida!');
        return redirect()->route('index');
    }
}
