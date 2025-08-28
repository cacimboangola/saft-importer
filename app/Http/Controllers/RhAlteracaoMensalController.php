<?php

namespace App\Http\Controllers;

use App\Http\Resources\RhAlteracaoMensalResource;
use App\Http\Services\RhAlteracaoMensalService;
use App\Models\RhAlteracaoMensal;
use Illuminate\Http\Request;

class RhAlteracaoMensalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($nif, Request $request)
    {
        return RhAlteracaoMensalResource::collection(RhAlteracaoMensalService::getAllRhAlteracoesMensais($nif, $request));
    }
    public function getAllRhAlteracoesMensaisByFuncionarioId($nif, $funcionario_id, Request $request)
    {
        return RhAlteracaoMensalResource::collection(RhAlteracaoMensalService::getAllRhAlteracoesMensaisByFuncionarioId($nif,$funcionario_id, $request));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //return new RhAlteracaoMensalResource(RhAlteracaoMensalService::storeRhAlteracaoMensal($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new RhAlteracaoMensalResource(RhAlteracaoMensalService::storeRhAlteracaoMensal($request));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RhAlteracaoMensal  $rhAlteracaoMensal
     * @return \Illuminate\Http\Response
     */
    public function show(RhAlteracaoMensal $rhAlteracaoMensal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RhAlteracaoMensal  $rhAlteracaoMensal
     * @return \Illuminate\Http\Response
     */
    public function edit(RhAlteracaoMensal $rhAlteracaoMensal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RhAlteracaoMensal  $rhAlteracaoMensal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RhAlteracaoMensal $rhAlteracaoMensal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RhAlteracaoMensal  $rhAlteracaoMensal
     * @return \Illuminate\Http\Response
     */
    public function destroy(RhAlteracaoMensal $rhAlteracaoMensal)
    {
        //
    }
}
