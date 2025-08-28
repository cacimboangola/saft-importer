<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocsRequest;
use App\Http\Requests\UpdateDocsRequest;
use App\Http\Resources\DocResource;
use App\Http\Services\AnalyticsService;
use App\Http\Services\DocInventarioService;
use App\Http\Services\DocPontoService;
use App\Http\Services\DocService;
use App\Models\Docs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class DocsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       // return DocResource::collection(array_merge(DocService::getDocsPurchases(),DocService::getDocsSales()));
        //return DocResource::collection(DocService::getDocsSalesDetailsWithLines());
    }

    public function documents(Request $request, $nif)
    {
        //dd(DocInventario::getInventoryValorizationByNif($nif, $request)['0']['qtd_artigos']);

        $data = array_merge(
                            DocService::getDocsSalesDraft($request, $nif),
                            DocInventarioService::getInventoryValorizationByNif($nif, $request),
                            DocService::getDocsPurchases($request, $nif),
                            DocService::getDocsSales($request, $nif),
                            DocService::getDocsWorkingDocuments($request, $nif),
                            DocService::getDocsPayments($request, $nif)
                            //DocPontoService::getTotalPontos($nif)
                        );
        return DocResource::collection($data);
    }

    public function documentsInventoryDetails(Request $request, $nif)
    {
        $data = DocInventarioService::getInventoryValorizationLinesByNif($nif, $request);
        return response()->json($data, 200);
    }

    public function analyticsForCharts(Request $request, $nif)
    {
        return DocResource::collection(DocService::analyticsForCharts($request, $nif));
    }

    public function documentsCustomerBalance(Request $request, $nif)
    {
        $data = DocService::getCustomerBalances($request, $nif);
        return DocResource::collection($data);
    }
    public function documentsCustomerBalanceV2(Request $request, $nif)
    {
        $data = DocService::getCustomerBalancesV2($request, $nif);
        return DocResource::collection($data);
    }
    public function documentsCustomerCurrentAccount(Request $request, $nif, $customerId)
    {
        $data = DocService::getCustomerCurrentAccount($request, $nif, $customerId);
        return DocResource::collection($data);
    }
    public function documentsCustomerCurrentAccountDetails(Request $request, $nif, $customerId)
    {
        $data = DocService::getCustomerCurrentAccountDetails($request, $nif, $customerId);
        return DocResource::collection($data);
    }

    public function documentsSalesDetails(Request $request, $nif)
    {
        return DocResource::collection(DocService::getDocsSalesDetailsWithLines($request, $nif));
    }
    public function documentsDraftsDetails(Request $request, $nif)
    {
        return DocResource::collection(DocService::getDocsDraftsDetailsWithLines($request, $nif));
    }

    public function documentsWorkingsDetails(Request $request, $nif){
        return DocResource::collection(DocService::getDocsWorkingDetailsWithLines($request, $nif));

    }
    public function documentsSalesWithPaymentsDetails(Request $request, $nif)
    {
        return DocResource::collection(DocService::getDocsSalesDetailsWithPayments($request, $nif));
    }

    public function documentsPurchasesDetails(Request $request, $nif)
    {
        return DocResource::collection(DocService::getDocsPurchasesDetailsWithLines($request, $nif));
    }

    public function analytics(Request $request, $nif) {
        return AnalyticsService::getTop10ClientsDetails($request, $nif);

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
     * @param  \App\Http\Requests\StoreDocsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       return new DocResource(DocService::insertDocAndLines($request));
    }

    public function purchaseOrderOnline(Request $request)
    {
       return new DocResource(DocService::insertDocAndLinesForOnlineShop($request));
    }

    public function getLastDocStatusCode($invoiceId){
        return new DocResource(DocService::getLastDocStatusCode($invoiceId));
    }

    public function getDocsDraftsOrderDetailsWithLines(Request $request, $nif) {
        return DocResource::collection(DocService::getDocsDraftsOrderDetailsWithLines($request, $nif));
    }
    public function getDocsDraftOrderDetailsWithLinesExpositores(Request $request, $user_id) {
        return DocResource::collection(DocService::getDocsDraftOrderDetailsWithLinesExpositores($request, $user_id));
    }

    public function getDocsDraftsOrderDetailsWithLinesByID($invoice_id)  {
       // return new DocResource(DocService::getDocsDraftsOrderDetailsWithLinesByID($invoice_id));

        return response()->json(DocService::getDocsDraftsOrderDetailsWithLinesByID($invoice_id), 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Docs  $docs
     * @return \Illuminate\Http\Response
     */
    public function show(Docs $docs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Docs  $docs
     * @return \Illuminate\Http\Response
     */
    public function edit(Docs $docs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocsRequest  $request
     * @param  \App\Models\Docs  $docs
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocsRequest $request, Docs $docs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Docs  $docs
     * @return \Illuminate\Http\Response
     */
    public function destroy(Docs $docs)
    {
        //
    }

    public function deleteDocDraft($invoiceId)
    {
        DocService::deleteDocDraft($invoiceId);
    }
    //
    public function updateDocDraft($invoiceId, Request $request)
    {
        DocService::updateDocDraft($invoiceId, $request);
    }

    public function getQrCode() {

        // Supondo que a imagem base64 seja enviada no campo 'image'
        $qrCode = (new \chillerlan\QRCode\QRCode())->render("https://cacimboerp.com");
        $base64Image = $qrCode;

        // Remover a parte do cabealho "data:image/svg+xml;base64,"
        list($type, $base64Image) = explode(';', $base64Image);
        list(, $base64Image) = explode(',', $base64Image);

        // Decodificar a imagem base64
        $imageData = base64_decode($base64Image);

        // Definir um nome único para a imagem
        $fileName = uniqid() . '.svg';

        // Salvar a imagem no disco configurado
        Storage::put('images/' . $fileName, $imageData);

        return Storage::download('images/' . $fileName);

        // Retornar a resposta com o caminho da imagem salva
        return response()->json([
            'success' => true,
            'file_path' => 'images/' . $fileName
        ]);
    }

}
