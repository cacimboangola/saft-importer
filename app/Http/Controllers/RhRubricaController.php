<?php

namespace App\Http\Controllers;

use App\Http\Resources\RhRubricaResource;
use App\Http\Services\RhRubricaService;
use App\Models\RhRubrica;
use Illuminate\Http\Request;
class RhRubricaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($nif)
    {
        return RhRubricaResource::collection(RhRubricaService::getAllRhRubricas($nif));
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
        //return new RhRubricaResource(RhRubricaService::getAllRhRubricas()); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RhRubrica  $rhRubrica
     * @return \Illuminate\Http\Response
     */
    public function show(RhRubrica $rhRubrica)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RhRubrica  $rhRubrica
     * @return \Illuminate\Http\Response
     */
    public function edit(RhRubrica $rhRubrica)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RhRubrica  $rhRubrica
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RhRubrica $rhRubrica)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RhRubrica  $rhRubrica
     * @return \Illuminate\Http\Response
     */
    public function destroy(RhRubrica $rhRubrica)
    {
        //
    }
}
