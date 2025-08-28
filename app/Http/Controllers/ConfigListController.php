<?php

namespace App\Http\Controllers;

use App\Models\ConfigList;
use Illuminate\Http\Request;

class ConfigListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = ConfigList::get();
        return response()->json($config, 200);
    }

    public function configListForCompany(Request $request, $companyID)  {
        
        $fields = $request->query('fields');

        if ($fields) {
            $allowedFields = array_merge(['CompanyID'], array_keys((new ConfigList)->getCasts()));
            $selectedFields = array_intersect(explode(',', $fields), $allowedFields);
    
            if (empty($selectedFields)) {
                return response()->json(["message" => "Nenhum campo válido foi solicitado."], 400);
            }
        } else {
            $selectedFields = ['*'];
        }

        $config = ConfigList::select($selectedFields)->where("CompanyID",$companyID)->get();
        
        return response()->json($config, 200);
    }

    public function getLocalidadesForCompany($companyID)  {
        $config = ConfigList::select("localidades")->where("CompanyID",$companyID)->get();
        return response()->json($config, 200);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConfigList  $configList
     * @return \Illuminate\Http\Response
     */
    public function show(ConfigList $configList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConfigList  $configList
     * @return \Illuminate\Http\Response
     */
    public function edit(ConfigList $configList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConfigList  $configList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConfigList $configList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConfigList  $configList
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConfigList $configList)
    {
        //
    }
}
