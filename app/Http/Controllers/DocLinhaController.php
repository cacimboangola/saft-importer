<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocLinhaRequest;
use App\Http\Requests\UpdateDocLinhaRequest;
use App\Http\Resources\DocLinhaResource;
use App\Http\Services\DocLinhaService;
use App\Models\DocLinha;
use Illuminate\Http\Request;

class DocLinhaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return DocLinhaResource::collection(DocLinhaService::getAllDocLinhas());
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
     * @param  \App\Http\Requests\StoreDocLinhaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new DocLinhaResource(DocLinhaService::insertDocLinhaApi($request));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocLinha  $docLinha
     * @return \Illuminate\Http\Response
     */
    public function show(DocLinha $docLinha)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocLinha  $docLinha
     * @return \Illuminate\Http\Response
     */
    public function edit(DocLinha $docLinha)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocLinhaRequest  $request
     * @param  \App\Models\DocLinha  $docLinha
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocLinhaRequest $request, DocLinha $docLinha)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocLinha  $docLinha
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocLinha $docLinha)
    {
        //
    }
}
