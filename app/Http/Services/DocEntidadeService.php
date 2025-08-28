<?php

namespace App\Http\Services;

use App\Models\DocEntidade;
use App\Models\DocEntidadesNumber;

class DocEntidadeService
{
    public static function insertDocEntidade($request){
        $data['customer_id'] = $request->customer_id;
        $data['account_id'] = $request->account_id;
        $data['customer_tax_id'] = $request->customer_tax_id;
        $data['company_name'] = $request->company_name;
        $data['contact'] = $request->contact;
        $data['adress_detail'] = $request->adress_detail;
        $data['city'] = $request->city;
        $data['country'] = $request->country;
        $data['telefone'] = $request->telefone;
        $data['email'] = $request->email;
        $docEntidade = DocEntidade::create($data);
        return $docEntidade;
    }

    public static function insertDocEntidadeBySaft($request, $nif){
        $data['CustomerID'] = $nif."-".$request['CustomerID'];
        $data['AccountID'] = $request['AccountID'];
        $data['CustomerTaxID'] = $request['CustomerTaxID'];
        $data['CompanyName'] = $request['CompanyName'];
        $data['Contact'] = $request['Contact'];
        $data['AddressDetail'] = $request['BillingAddress']['AddressDetail'];
        $data['City'] = $request['BillingAddress']['City'];
        $data['Country'] = $request['BillingAddress']['Country'];
        $docEntidade = DocEntidade::UpdateOrCreate(["CustomerID" => $data['CustomerID']],$data);
        return $docEntidade;
    }

    public static function insertDocEntidadeApi($request){
        $docEnt =  DocEntidade::where(["CustomerTaxID" => $request['nif'], "CompanyID" => $request['empresa_nif']])->first();
        if ($docEnt == null) {
            $doc_entidade_number = DocEntidadesNumber::create([
                'CompanyID' => $request['empresa_nif'],
            ]);
        $doc_entidade_number->update(['temp_CustomerID' => $request['empresa_nif'] . '-temp-' . $doc_entidade_number->id]);
        $data['CustomerID'] = $doc_entidade_number->temp_CustomerID;

        }else{
            $data['CustomerID'] = $docEnt->CustomerID;
        }
        $data['AccountID'] = 0;
        $data['CustomerTaxID'] = $request['nif'];
        $data['CompanyName'] = $request['nome'];
        $data['CompanyID'] = $request['empresa_nif'];
        $data['Contact'] = $request['contacto_nome'];
        $data['AddressDetail'] = $request['morada'];
        $data['City'] = $request['localidade'];
        $data['Country'] = $request['pais'];
        $data['Telefone'] = $request['telefone'];
        $data['Email'] = $request['email'];
        $data['temp_CustomerID'] = $data['CustomerID'];
        //$docEntidade = DocEntidade::create($data);
        $docEntidade = DocEntidade::UpdateOrCreate(["CustomerTaxID" => $data['CustomerTaxID'], "CompanyID" => $data['CompanyID']],$data);
        return $docEntidade;
    }

    public static function getAllDocEntidades(){
        $docEntidades = DocEntidade::all();
        return $docEntidades;
    }
    public static function getAllDocEntidadesByCompanyID($nif){
        $docEntidades = DocEntidade::where("CompanyID", $nif)->get();
        return $docEntidades;
    }

}
