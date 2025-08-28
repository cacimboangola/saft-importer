<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoredocNumberRequest;
use App\Http\Requests\UpdatedocNumberRequest;
use App\Models\docNumber;

class DocNumberController extends Controller
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
     * @param  \App\Http\Requests\StoredocNumberRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoredocNumberRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\docNumber  $docNumber
     * @return \Illuminate\Http\Response
     */
    public function show(docNumber $docNumber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\docNumber  $docNumber
     * @return \Illuminate\Http\Response
     */
    public function edit(docNumber $docNumber)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatedocNumberRequest  $request
     * @param  \App\Models\docNumber  $docNumber
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatedocNumberRequest $request, docNumber $docNumber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\docNumber  $docNumber
     * @return \Illuminate\Http\Response
     */
    public function destroy(docNumber $docNumber)
    {
        //
    }
}
