<?php

namespace App\Http\Services;

use App\Models\DocEmpresa;
use App\Models\User;
use App\Models\UserEmpresa;

class DocEmpresaService
{
    public static function insertDocEmpresa($request){
        $data['CompanyID'] = $request->tax_registration_number . "-1";
        $data['TaxRegistrationNumber'] = $request->tax_registration_number;
        $data['CompanyName'] = $request->company_name;
        $data['CurrencyCode'] = $request->currency_code;
        $data['UserID'] = $request->user_id;
        $data['UserEmail'] = $request->user_email;
        $data['Country'] = $request->country;
        $data['AdressDetail'] = $request->adress_detail;
        $data['City'] = $request->city;
        $data['Email'] = $request->email;
        $data['Telefone'] = $request->telefone;
        $data['UserName'] = $request->user_name;
        $docEmpresa = DocEmpresa::create($data);
        return $docEmpresa;
    }
    public static function insertDocEmpresaBySaft($request){
        $data['CompanyID'] = $request['CompanyID'];
        $data['TaxRegistrationNumber'] = $request['TaxRegistrationNumber'];
        $data['CompanyName'] = $request['CompanyName'];
        $data['CurrencyCode'] = $request['CurrencyCode'];
        $docEmpresa = DocEmpresa::UpdateOrCreate(["CompanyID" => $data['CompanyID']], $data);
        return $docEmpresa;
    }

    public static function getAllDocEmpresas(){
        $docEmpresas = DocEmpresa::all();
        return $docEmpresas;
    }
    public static function getAllDocEmpresasByNif($nif){
        $empresas = DocEmpresa::query()
                            ->where("TaxRegistrationNumber", $nif)
                            ->orderBy("CompanyName")
                            ->get();
        return $empresas;
    
    }

    public static function getEmpresasByUserId($user_id, $lastUsedId){
        $empresas = DocEmpresa::query()
                                ->join('user_empresas', 'user_empresas.CompanyID', '=', 'doc_empresas.CompanyID')
                                ->select('doc_empresas.*')
                                ->where('user_empresas.user_id', $user_id)
                                ->where('doc_empresas.CompanyID', '<>', '')
                                ->orderByRaw("FIELD(doc_empresas.CompanyID, '$lastUsedId', doc_empresas.CompanyID), doc_empresas.CompanyName asc")
                                //->orderByRaw("case when doc_empresas.CompanyID = '$lastUsedId' then 0 else 1 end","asc")
                                ->get();
        return $empresas;
    }
  	public static function getEmpresasGestHotelByUserId($user_id, $lastUsedId){
    	$response = file_get_contents("https://cacimboweb.com/api/empresas/get-nif/licencas/modulo/30");
        $nifs = json_decode($response, true);
        $empresas = DocEmpresa::query()
                                ->join('user_empresas', 'user_empresas.CompanyID', '=', 'doc_empresas.CompanyID')
                                ->select('doc_empresas.*')
                                ->where('user_empresas.user_id', $user_id)
                                ->where('doc_empresas.CompanyID', '<>', '')
          						->whereIn('TaxRegistrationNumber', $nifs)
                                ->orderByRaw("FIELD(doc_empresas.CompanyID, '$lastUsedId', doc_empresas.CompanyID), doc_empresas.CompanyName asc")
                                //->orderByRaw("case when doc_empresas.CompanyID = '$lastUsedId' then 0 else 1 end","asc")
                                ->get();
        return $empresas;
    }
    public static function getUsersByCompanyId($companyId){
       /* $users = User::query()
                                ->join('user_empresas', 'user_empresas.user_id', '=', 'users.id')
                                ->where('user_empresas.CompanyID', $companyId)
                                ->get();*/
        $usersCollection = DocEmpresa::with('user_empresas.user')
                            ->where('CompanyID', $companyId)
                            ->get();
        $users = $usersCollection->pluck('user_empresas')->flatten()->pluck("user");
        $users->all();
        //$users = $usersPlucked->pluck('user');
        return $users;
    }


}
