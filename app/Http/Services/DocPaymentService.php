<?php

namespace App\Http\Services;

use App\Models\DocPayment;
use App\Models\UserEmpresa;

class DocPaymentService
{

    public function insertPaymentApi($request)
    {
        $data['PaymentDate'] = $request->PaymentDate;
        $data['PaymentRefNo'] = $request->PaymentRefNo ; 
        $data['PaymentType'] = $request->PaymentType ;
        $data['PaymentStatus'] = $request->PaymentStatus ;
        $data['SourceDocumentID'] = $request->SourceDocumentID ;
        $data['OriginatingON'] = $request->OriginatingON ;
        $data['PaymentMechanism'] = $request->PaymentMechanism ;
        $data['PaymentAmount'] = $request->PaymentAmount ;
        $data['CreditAmount'] = $request->CreditAmount ;
        $data['DebitAmount'] = $request->DebitAmount ;
        $data['PaymentMechanismDescription'] = $request->PaymentMechanismDescription ;
        $data['CustomerID'] = $request->CustomerID ;
        $data['CompanyID'] = $request->CompanyID ;
        $data['AccountID'] = $request->AccountID ;
        $data['AccountNum'] = $request->AccountNum ;
        $data['AccountBank'] = $request->AccountBank ;
       $doc_payment = DocPayment::create($data);
        return $doc_payment;

    }
}