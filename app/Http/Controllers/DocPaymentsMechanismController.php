<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocPaymentsMechanismRequest;
use App\Http\Requests\UpdateDocPaymentsMechanismRequest;
use App\Http\Resources\DocPaymentsMechanismResource;
use App\Http\Services\DocPaymentsMechanismsService;
use App\Models\DocPaymentsMechanism;

class DocPaymentsMechanismController extends Controller
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

    public function allPaymentsMechanismsByCompanyID($company_id)
    {
        return DocPaymentsMechanismResource::collection(DocPaymentsMechanismsService::getAllPaymetsMechanismsByCompany($company_id));
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
     * @param  \App\Http\Requests\StoreDocPaymentsMechanismRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocPaymentsMechanismRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocPaymentsMechanism  $docPaymentsMechanism
     * @return \Illuminate\Http\Response
     */
    public function show(DocPaymentsMechanism $docPaymentsMechanism)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocPaymentsMechanism  $docPaymentsMechanism
     * @return \Illuminate\Http\Response
     */
    public function edit(DocPaymentsMechanism $docPaymentsMechanism)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocPaymentsMechanismRequest  $request
     * @param  \App\Models\DocPaymentsMechanism  $docPaymentsMechanism
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocPaymentsMechanismRequest $request, DocPaymentsMechanism $docPaymentsMechanism)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocPaymentsMechanism  $docPaymentsMechanism
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocPaymentsMechanism $docPaymentsMechanism)
    {
        //
    }
}
