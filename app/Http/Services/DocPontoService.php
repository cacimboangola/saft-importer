<?php

namespace App\Http\Services;

use App\Models\DocEmpresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DocPontoService
{
    public static function getPontos($nifCliente, $nifEmp)
    {
        $response =  Http::get("http://ponto.cacimboweb.com/api/empresa/".$nifEmp."/pontos/cliente/".$nifCliente);

        return json_decode($response->getBody(), true);
    }

    public static function getTotalPontos($nifCliente)
    {
        $empresa = DocEmpresa::where("CompanyID", $nifCliente)->first();
        $response =  Http::get("http://pontos.cacimboweb.com/api/pontos/cliente/".$empresa->TaxRegistrationNumber."/total");
        $responseEmpresas =  Http::get("http://pontos.cacimboweb.com/api/pontos/cliente/".$empresa->TaxRegistrationNumber."/empresas-total");

        $data = json_decode($response->getBody(), true);
        $dataDocs = json_decode($responseEmpresas->getBody(), true);
        $dataArray = $data;
        $dataArray['0']["key"] = "Pontos";
        $dataArray['0']["description"] = "Pontos";
        $dataArray['0']["documents"] = $dataDocs;
        return $dataArray;
    }
}