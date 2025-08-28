<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocBankAccountRequest;
use App\Http\Requests\UpdateDocBankAccountRequest;
use App\Http\Resources\DocBankAccountResource;
use App\Http\Services\DocBankAccountService;
use App\Models\DocBankAccount;


class DocBankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
    }
    public function allDocBankAcountsByCompanyID($company_id)
    {
        return DocBankAccountResource::collection(DocBankAccountService::getAllDocBankAccountsByCompany($company_id));
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
     * @param  \App\Http\Requests\StoreDocBankAccountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocBankAccountRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocBankAccount  $docBankAccount
     * @return \Illuminate\Http\Response
     */
    public function show(DocBankAccount $docBankAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocBankAccount  $docBankAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(DocBankAccount $docBankAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocBankAccountRequest  $request
     * @param  \App\Models\DocBankAccount  $docBankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocBankAccountRequest $request, DocBankAccount $docBankAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocBankAccount  $docBankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocBankAccount $docBankAccount)
    {
        //
    }
}
