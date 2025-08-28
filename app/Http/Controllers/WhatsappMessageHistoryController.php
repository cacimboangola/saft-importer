<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWhatsappMessageHistoryRequest;
use App\Http\Requests\UpdateWhatsappMessageHistoryRequest;
use App\Http\Resources\WhatsappMessageHistoryResource;
use App\Models\WhatsappMessageHistory;
use Illuminate\Http\Request;

class WhatsappMessageHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($number)
    {
        $messageWhatsapp = WhatsappMessageHistory::query()
        ->orWhere(function($q){
            $q->where("message_status", "waiting")
             ->orWhereNull("message_status");
        })
        ->where("to_number", $number)
        ->get();
        return WhatsappMessageHistoryResource::collection(
            $messageWhatsapp
        );
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
     * @param  \App\Http\Requests\StoreWhatsappMessageHistoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WhatsappMessageHistory  $whatsappMessageHistory
     * @return \Illuminate\Http\Response
     */
    public function show(WhatsappMessageHistory $whatsappMessageHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WhatsappMessageHistory  $whatsappMessageHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(WhatsappMessageHistory $whatsappMessageHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWhatsappMessageHistoryRequest  $request
     * @param  \App\Models\WhatsappMessageHistory  $whatsappMessageHistory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWhatsappMessageHistoryRequest $request, WhatsappMessageHistory $whatsappMessageHistory)
    {
        //
    }
    public function updateMessageStatus(Request $request)
    {
        $data = WhatsappMessageHistory::when($request->message_status == "waiting", function($q) use($request){
            $q->where(['to_number' => $request->number, 'message_status' => 'failed']);
        })
        ->when($request->message_status == "concluido", function($q) use($request){
            $q->orWhere(function($q){
                $q->where("message_status", "waiting")
                 ->orWhereNull("message_status");
            });
        })
        ->when($request->message_status == "failed", function($q) use($request){
            $q->where(['to_number' => $request->number, 'message_status' => null]);
        })
        ->update(['message_status' => $request->message_status]);
        return response()->json($data, 200);
        
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WhatsappMessageHistory  $whatsappMessageHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(WhatsappMessageHistory $whatsappMessageHistory)
    {
        //
    }
}
