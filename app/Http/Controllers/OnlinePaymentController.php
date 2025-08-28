<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOnlinePaymentRequest;
use App\Http\Requests\UpdateOnlinePaymentRequest;
use App\Http\Services\OnlinePaymentResource;
use App\Http\Services\OnlinePaymentService;
use App\Models\OnlinePayment;
use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
    protected $paymentService;

    public function __construct(OnlinePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'CompanyID' => 'required',
            'PaymentDate' => 'required',
            'SourceDocumentID' => 'required',
            'PaymentMechanism' => 'required',
            'PaymentAmount' => 'required',
        ]);
        /*$data['CompanyID'] = $request->
        $data['PaymentDate'] = $request->
        $data['SourceDocumentID'] = $request->
        $data['PaymentMechanism'] = $request->
        $data['PaymentAmount'] = $request->*/

        $payment = $this->paymentService->createPayment($data);

        return response()->json($payment, 201);
    }

    public function payForOnlineDoc(Request $request) {
       return new OnlinePaymentResource($this->paymentService->payForOnlineDoc($request));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'CompanyID' => 'required',
            'PaymentDate' => 'required',
            'SourceDocumentID' => 'required',
            'PaymentMechanism' => 'required',
            'PaymentAmount' => 'required',
        ]);

        $payment = $this->paymentService->getPaymentById($id);

        $payment = $this->paymentService->updatePayment($payment, $data);

        return response()->json($payment, 200);
    }

    public function delete($id)
    {
        $payment = $this->paymentService->getPaymentById($id);

        $this->paymentService->deletePayment($payment);

        return response()->json(null, 204);
    }

    public function show($id)
    {
        $payment = $this->paymentService->getPaymentById($id);

        return response()->json($payment, 200);
    }

    public function index()
    {
        $payments = $this->paymentService->getAllPayments();

        return response()->json($payments, 200);
    }
}
