<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocEntidadeRequest;
use App\Http\Requests\UpdateDocEntidadeRequest;
use App\Http\Resources\DocEntidadeResource;
use App\Http\Services\DocEntidadeService;
use App\Models\DocEntidade;
use Illuminate\Http\Request;

class DocEntidadeController extends Controller
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

    public function getAllDocEntidadesByCompanyID($nif)
    {
        return DocEntidadeResource::Collection(DocEntidadeService::getAllDocEntidadesByCompanyID($nif));
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
     * @param  \App\Http\Requests\StoreDocEntidadeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new DocEntidadeResource(DocEntidadeService::insertDocEntidadeApi($request));        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocEntidade  $docEntidade
     * @return \Illuminate\Http\Response
     */
    public function show(DocEntidade $docEntidade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocEntidade  $docEntidade
     * @return \Illuminate\Http\Response
     */
    public function edit(DocEntidade $docEntidade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocEntidadeRequest  $request
     * @param  \App\Models\DocEntidade  $docEntidade
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocEntidadeRequest $request, DocEntidade $docEntidade)
    {
        //
    }

    public function changeWhatsappStatus(DocEntidade $docEntidade)
    {
        //dd($docEntidade);
       $docEntidade->update(['whatsapp'=>"1"]);
       //dd($docEntidade);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocEntidade  $docEntidade
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocEntidade $docEntidade)
    {
        //
    }
}
