<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOnlineStoreCategoryRequest;
use App\Http\Requests\UpdateOnlineStoreCategoryRequest;
use App\Models\OnlineStoreCategory;

class OnlineStoreCategoryController extends Controller
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
     * @param  \App\Http\Requests\StoreOnlineStoreCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOnlineStoreCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OnlineStoreCategory  $onlineStoreCategory
     * @return \Illuminate\Http\Response
     */
    public function show(OnlineStoreCategory $onlineStoreCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OnlineStoreCategory  $onlineStoreCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(OnlineStoreCategory $onlineStoreCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOnlineStoreCategoryRequest  $request
     * @param  \App\Models\OnlineStoreCategory  $onlineStoreCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOnlineStoreCategoryRequest $request, OnlineStoreCategory $onlineStoreCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OnlineStoreCategory  $onlineStoreCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(OnlineStoreCategory $onlineStoreCategory)
    {
        //
    }
}
