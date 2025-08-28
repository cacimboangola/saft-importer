<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocEmpresaRequest;
use App\Http\Requests\UpdateDocEmpresaRequest;
use App\Http\Resources\DocEmpresaResource;
use App\Http\Resources\UserResource;
use App\Http\Services\DocEmpresaService;
use App\Models\DocEmpresa;
use App\Models\SocketInfo;
use Illuminate\Http\Request;

class DocEmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        
    }

    public function getEmpresasByUserId($user_id){
       return DocEmpresaResource::collection(DocEmpresaService::getEmpresasByUserId($user_id, null));
    }
    public function showClientesOnline(Request $request){
		// Recupere todos os registros do modelo SocketInfo
        $query = SocketInfo::query();

        // Aplicar filtros com base em query strings, se fornecidos
        if ($request->has('estado')) {
            $query->where('clientes.estado', $request->estado);
        }

        if ($request->has('company_name')) {
            $query->where('clientes.company_name', 'LIKE', '%' . $request->company_name . '%');
        }
      
      	if ($request->has('companyID')) {
            $query->where('clientes.CompanyID', 'LIKE', '%' . $request->companyID . '%');
        }
      
      	if ($request->has('cacimbo_version')) {
            $query->where('clientes.cacimbo_version', 'LIKE', '%' . $request->cacimbo_version . '%');
        }
     	 if ($request->has('city')) {
            $query->where('vps_cacimbo_api_erp.doc_empresas.City', $request->city);
        }
      	if ($request->has('country')) {
            $query->where('vps_cacimbo_api_erp.doc_empresas.Country', $request->country);
        }
		// Realize uma junção (join) com a tabela DocEmpresa usando a coluna CompanyID
        $query->Leftjoin('vps_cacimbo_api_erp.doc_empresas', 'clientes.CompanyID', '=', 'vps_cacimbo_api_erp.doc_empresas.CompanyID');

        // Selecione as colunas desejadas de ambas as tabelas
        $query->selectRaw('clientes.CompanyID, clientes.posto, clientes.company_name, clientes.estado, vps_cacimbo_api_erp.doc_empresas.CompanyName, 
        clientes.server, clientes.cacimbo_version,clientes.data, vps_cacimbo_api_erp.doc_empresas.AddressDetail ,vps_cacimbo_api_erp.doc_empresas.City ,
        vps_cacimbo_api_erp.doc_empresas.Country');
        
      	$query->where('clientes.CompanyID', '<>', "");

        // Execute a consulta
        $socketInfos = $query->orderByRaw("clientes.company_name asc, City asc")->get();

        return response()->json(['data' => $socketInfos]);
    }


    
    public function getUsersByCompanyId($companyId){
        return UserResource::collection(DocEmpresaService::getUsersByCompanyId($companyId));
    }

    public function getAllDocsEmpresas(){
        return UserResource::collection(DocEmpresaService::getAllDocEmpresas());
    }

    
    public function getAllDocsEmpresasByNif($nif){
        return DocEmpresaResource::collection(DocEmpresaService::getAllDocEmpresasByNif($nif));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDocEmpresaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocEmpresaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocEmpresa  $docEmpresa
     * @return \Illuminate\Http\Response
     */
    public function show(DocEmpresa $docEmpresa)
    {
        //go
        return new DocEmpresaResource($docEmpresa);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocEmpresa  $docEmpresa
     * @return \Illuminate\Http\Response
     */
    public function edit(DocEmpresa $docEmpresa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocEmpresaRequest  $request
     * @param  \App\Models\DocEmpresa  $docEmpresa
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocEmpresaRequest $request, DocEmpresa $docEmpresa)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocEmpresa  $docEmpresa
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocEmpresa $docEmpresa)
    {
        //
    }
}
