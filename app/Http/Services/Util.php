<?php

namespace App\Http\Services;

use App\Http\Services\DocLinhaResource;
use App\Http\Services\DocService;
use Illuminate\Support\Facades\Storage;
class Util {

    public static function saftUpload($request)
    {
        $fileName = $request->file('file')->getClientOriginalName();
        $path = Storage::putFile('uploads',  $request->file('file'));
        $saft = $request->file('file')->move('uploads/safts/',$fileName);
        return $path;
    }

    public static function readXMLAndReturnAnArray($fileName){
        $content = Storage::get($fileName);
        $xmlObject = simplexml_load_string($content);
        $json = json_encode($xmlObject);
        $phpArray = json_decode($json, true); 
        $nif = $phpArray["Header"]['CompanyID'];
        DocEmpresaService::insertDocEmpresaBySaft($phpArray["Header"]);
        foreach ($phpArray['MasterFiles']['Customer'] as $customer) {
            DocEntidadeService::insertDocEntidadeBySaft($customer, $nif);
        }
        foreach ($phpArray['SourceDocuments']['SalesInvoices']['Invoice'] as $invoice) {
            $invoiceMerged = array_merge($invoice, ['empresa_nif' => $nif]);
            $invoiceData = DocService::insertDocBySaft($invoiceMerged, $nif);
            DocLinhaService::insertDocLinhaBySaft($invoiceMerged, $invoiceData);
        }
        
    }

    
}
