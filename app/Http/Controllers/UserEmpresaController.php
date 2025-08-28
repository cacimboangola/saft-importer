<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserEmpresaRequest;
use App\Http\Requests\UpdateUserEmpresaRequest;
use App\Http\Resources\DocEmpresaResource;
use App\Http\Resources\UserResource;
use App\Http\Services\DocEmpresaService;
use App\Http\Services\UserService;
use App\Models\User;
use App\Models\UserEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class UserEmpresaController extends Controller
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    public function getAllDocEmpresasByNif($nif)
    {
        return DocEmpresaResource::collection(DocEmpresaService::getAllDocEmpresasByNif($nif));
    }
    public function associateUserToCompany(Request $request){
        $data['CompanyID'] = $request->companyId;
        $data["user_id"] = $request->user_id;
        $user_empresa = UserEmpresa::create($data);
        if($user_empresa != null){
            try {
                Http::get("https://cacimboweb.com/api/sync-permissions-with-docs/empresa/". $request->companyId);
                return response()->json($user_empresa, 201);
            } catch (\Throwable $th) {
                return response()->json($user_empresa, 201);
            }
            
        }else{
            return response()->json([], 400);
        }
    }

    public function changeLastCompanyIDUsed(User $user, Request $request){
        $data = UserService::changeLastCompanyIDUsed($user, $request->companyId);
        if ($data != null) {
            return response()->json($user, 200);
        }else {
            return response()->json([], 400);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserEmpresaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserEmpresaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserEmpresa  $userEmpresa
     * @return \Illuminate\Http\Response
     */
    public function show(UserEmpresa $userEmpresa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserEmpresa  $userEmpresa
     * @return \Illuminate\Http\Response
     */
    public function edit(UserEmpresa $userEmpresa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserEmpresaRequest  $request
     * @param  \App\Models\UserEmpresa  $userEmpresa
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserEmpresaRequest $request, UserEmpresa $userEmpresa)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserEmpresa  $userEmpresa
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserEmpresa $userEmpresa)
    {
        //
    }
}
