<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocStatusRequest;
use App\Http\Requests\UpdateDocStatusRequest;
use App\Models\DocStatus;

class DocStatusController extends Controller
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDocStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocStatus  $docStatus
     * @return \Illuminate\Http\Response
     */
    public function show(DocStatus $docStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocStatus  $docStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(DocStatus $docStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocStatusRequest  $request
     * @param  \App\Models\DocStatus  $docStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocStatusRequest $request, DocStatus $docStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocStatus  $docStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocStatus $docStatus)
    {
        //
    }
}
