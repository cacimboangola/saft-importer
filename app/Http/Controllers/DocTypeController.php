<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocTypeRequest;
use App\Http\Requests\UpdateDocTypeRequest;
use App\Http\Resources\DocTypeResource;
use App\Http\Services\DocTypeService;
use App\Models\DocType;

class DocTypeController extends Controller
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

    public function allDocTypeByCompanyID($company_id)
    {
        return DocTypeResource::collection(DocTypeService::getAllDocTypesByCompany($company_id));
    }
    public function allDocTypes()
    {
        return DocTypeResource::collection(DocTypeService::getAllDocTypes());
    }
    public function getDocType($id)
    {
        # code...
        return new DoctypeResource(DocTypeService::getDocType($id));
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
     * @param  \App\Http\Requests\StoreDocTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocType  $docType
     * @return \Illuminate\Http\Response
     */
    public function show(DocType $docType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocType  $docType
     * @return \Illuminate\Http\Response
     */
    public function edit(DocType $docType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocTypeRequest  $request
     * @param  \App\Models\DocType  $docType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocTypeRequest $request, DocType $docType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocType  $docType
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocType $docType)
    {
        //
    }
}
