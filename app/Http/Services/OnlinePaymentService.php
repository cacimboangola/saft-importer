<?php 

// app/Services/OnlinePaymentService.php

namespace App\Http\Services;

use App\Models\Docs;
use App\Models\OnlinePayment;
use Carbon\Carbon;

class OnlinePaymentService
{

    public function addFieldInArrayPayments($payments, $doc)
    {   

        $mapped = [];
        foreach ($payments as $payment) {
            $payment['CompanyID'] = $doc->CompanyID; 
            $payment['PaymentDate'] = Carbon::now();
            $payment['SourceDocumentID'] =  $doc->InvoiceId;
            $mappedArray = array_push($mapped, $payment);
        }
        return $mapped;
    }
    
    public function createPayment($data)
    {
        return OnlinePayment::create($data);
    }

    public function updatePayment(OnlinePayment $payment, $data)
    {
        $payment->update($data);
        return $payment;
    }

    public function deletePayment(OnlinePayment $payment)
    {
        $payment->delete();
    }

    public function getPaymentById($id)
    {
        return OnlinePayment::findOrFail($id);
    }

    /*public function payForOnlineDoc($request){
        $doc = Docs::find($request->invoice_id);
        $totalPagamentos = $doc->onlinePayments()->sum('PaymentAmount');
        if ($doc->GrossTotal <= $request->amout && $totalPagamentos < $doc->GrossTotal) {
            $data['CompanyID'] = $doc->CompanyID; 
            $data['PaymentDate'] = Carbon::now();
            $data['SourceDocumentID'] =  $doc->InvoiceId, 
            $data['PaymentMechanism'] = $request->payment_mechanism; 
            $data['PaymentAmount'] = $request->amout;
            return $this->createPayment($data); 
        }else{
            return null;
        }
    }*/

    public function payForOnlineDoc($request, $doc){
        $data = $this->addFieldInArrayPayments($request["payments"], $doc);
        $doc->onlinePayments()->createMany($data);
        return $doc->load("onlinePayments");
    }

    public function getAllPayments()
    {
        return OnlinePayment::all();
    }
}
