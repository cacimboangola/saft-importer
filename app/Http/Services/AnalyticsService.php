<?php

namespace App\Http\Services;

use App\Core\Util;
use App\Http\Services\DocEntidadeService;
use App\Http\Services\OnlinePaymentService;
use App\Models\DocEntidade;
use App\Models\DocEntidadesSaldos;
use App\Models\DocLinha;
use App\Models\DocNumber;
use App\Models\DocPayment;
use App\Models\DocStatus;
use App\Models\Docs;
use App\Models\User;
use App\Models\UsersAdress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AnalyticsService
{

    public static function analytics($request, $nif)
    {
        // ...

        if ($date_diff_days == 0 && $date_diff_months == 0) {
            $docs = self::generateCommonQuery($request, $nif)
                ->groupByRaw("
                    CASE WHEN TIME(created_at) BETWEEN '00:00:00' AND '05:59:59' THEN 'Madrugada'
                        WHEN TIME(created_at) BETWEEN '06:00:00' AND '11:59:59' THEN 'Manhã'
                        WHEN TIME(created_at) BETWEEN '12:00:00' AND '17:59:59' THEN 'Tarde'
                        WHEN TIME(created_at) BETWEEN '18:00:00' AND '23:59:59' THEN 'Noite'
                    END")
                ->get();
            
            return self::formatResult($docs, "Horas");
        }

        if ($date_diff_days > 0 && $date_diff_months == 0) {
            $docs = self::generateCommonQuery($request, $nif)
                ->orderByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate), Day(docs.InvoiceDate)')
                ->groupByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate), Day(docs.InvoiceDate)')
                ->get();

            return self::formatResult($docs, "Dias");
        }

        if ($date_diff_days > 0 && $date_diff_months > 0) {
            $docs = self::generateCommonQuery($request, $nif)
                ->orderByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate)')
                ->groupByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate)')
                ->get();

            return self::formatResult($docs, "Mes");
        }
    }

    private static function generateCommonQuery($request, $nif)
    {
        return Docs::query()
            ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral')
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->where('docs.SourceDocuments', 'SalesInvoices')
            ->where('CompanyID', $nif)
            ->where('sync_status', 0)
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            });
    }

    public static function generateDocSeries($request, $nif){
        $docsTotalCredit = Docs::query()
            ->selectRaw("SUM(CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.GrossTotal ELSE 0 END) as amount, InvoiceTypeSerie as name, IF(STRCMP(docs.InvoiceType,'RC') AND STRCMP(docs.InvoiceType,'NC'), 'Debito','Credito') as movimentType, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.TaxPayable ELSE 0 END as taxPayable, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.Settlement ELSE 0 END as settlement")
            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
            })
            ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->where('CompanyID', $nif)
            ->where('docs.SourceDocuments','SalesInvoices')
            ->where('sync_status', 0)
            ->groupBy("InvoiceTypeSerie")
            ->get();
        return $docsTotalCredit;
    }

    private static function formatResult($docs, $visualization)
    {
        $docsArray['0']['values'] = $docs->toArray();
        $docsArray['0']['Visualizacao'] = $visualization;
        return $docsArray;
    }

    public static function getDocsSales($request, $nif)
    {
        $docs = Docs::query()
            ->join('doc_entidades', 'docs.CustomerID', '=', 'doc_entidades.CustomerID')
            ->selectRaw('
                (SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral,
                SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_credito,
                SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_debito,
                SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END) as total_iva'
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->where('docs.SourceDocuments', 'SalesInvoices')
            ->where('docs.CompanyID', $nif)
            ->where('sync_status', 0)
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->get();

        $docsTotalCredit = Docs::query()
            ->selectRaw('
                SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as amount,
                InvoiceTypeSerie as name,
                IF(STRCMP(docs.InvoiceType,"RC") AND STRCMP(docs.InvoiceType,"NC"), "Debito", "Credito") as movimentType,
                CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END as taxPayable,
                CASE WHEN docs.InvoiceStatus <> "A" THEN docs.Settlement ELSE 0 END as settlement'
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->where('CompanyID', $nif)
            ->where('docs.SourceDocuments', 'SalesInvoices')
            ->where('sync_status', 0)
            ->groupBy("InvoiceTypeSerie")
            ->get();

        // Consolidate common code to simplify
        $docsTotalCreditArray = $docs['0']['total_geral']
            ? [
                '0' => [
                    'key' => "Vendas",
                    'description' => "Vendas",
                    'documents' => $docsTotalCredit->toArray(),
                ],
            ]
            : [];

        return $docsTotalCreditArray;
    }

    public static function getTop10Clients($request, $nif)
    {
        $topClients = Docs::query()
            ->join('doc_entidades', 'docs.CustomerID', '=', 'doc_entidades.CustomerID')
            ->selectRaw('
                doc_entidades.CustomerName,
                (SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral'
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->where('docs.SourceDocuments', 'SalesInvoices')
            ->where('docs.CompanyID', $nif)
            ->where('docs.sync_status', 0)
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->groupBy('doc_entidades.CustomerID', 'doc_entidades.CustomerName')
            ->orderByDesc('total_geral')
            ->limit(10)
            ->get();

        $result = [];
        foreach ($topClients as $client) {
            $result[] = [
                'CustomerName' => $client->CustomerName,
                'TotalGeral' => $client->total_geral,
            ];
        }

        return json_encode(['TopClients' => $result], JSON_PRETTY_PRINT);
    }


    public static function getTop10ClientsDetails($request, $nif)
    {
        $docs = Docs::query()
                ->join('doc_entidades', 'docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('
                    COALESCE((SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                    - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)), 0) as total_geral,
                    COALESCE(SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END), 0) as total_credito,
                    COALESCE(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END), 0) as total_debito,
                    COALESCE(SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END), 0) as total_iva'
                )
                ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                    $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
                })
                ->where('docs.SourceDocuments', 'SalesInvoices')
                ->where('docs.CompanyID', $nif)
                ->where('sync_status', 0)
                ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->get();

        $topClients = Docs::query()
            ->join('doc_entidades', 'docs.CustomerID', '=', 'doc_entidades.CustomerID')
            ->selectRaw('
                doc_entidades.CompanyName,
                (SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral,
                SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_credito,
                SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_debito'
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->where('docs.SourceDocuments', 'SalesInvoices')
            ->where('docs.CompanyID', $nif)
            ->where('docs.sync_status', 0)
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->groupBy('doc_entidades.CustomerID', 'doc_entidades.CompanyName')
            ->orderByDesc('total_geral')
            ->limit(10)
            ->get();

        $seriesDetails = Docs::query()
            ->selectRaw('
                InvoiceTypeSerie as series,
                SUM(CASE WHEN docs.InvoiceStatus <> "A" AND docs.InvoiceType = "NC" THEN docs.GrossTotal ELSE 0 END) as total_credito_serie,
                SUM(CASE WHEN docs.InvoiceStatus <> "A" AND docs.InvoiceType <> "NC" THEN docs.GrossTotal ELSE 0 END) as total_debito_serie'
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->where('docs.CompanyID', $nif)
            ->where('docs.SourceDocuments', 'SalesInvoices')
            ->where('docs.sync_status', 0)
            ->groupBy('InvoiceTypeSerie')
            ->get();

        $docsContas = DocPayment::query()
            ->selectRaw('AccountBank, AccountNum ,SUM(CreditAmount)  
            as entrada, SUM(DebitAmount) as saida')
            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                $q->whereBetween('PaymentDate',[$request->start_date,$request->end_date]);
            })
            ->where('CompanyID', $nif)
            ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                $q->whereDate('PaymentDate', Carbon::today());
            })
            ->groupBy(['AccountBank','AccountNum'])
            ->get();

        $topProducts = DocLinha::query()
            ->join('vps_cacimbo_api_erp.docs', 'vps_cacimbo_api_erp.docs.invoiceId', '=', 'doc_linhas.invoiceId')
            ->selectRaw('
            doc_linhas.ProductCode,
            doc_linhas.ProductDescription,
            (SUM(CASE WHEN vps_cacimbo_api_erp.docs.InvoiceType <> "NC" AND vps_cacimbo_api_erp.docs.InvoiceType <> "A" THEN doc_linhas.Quantity ELSE 0 END)
                - SUM(CASE WHEN vps_cacimbo_api_erp.docs.InvoiceType = "NC" AND vps_cacimbo_api_erp.docs.InvoiceType <> "A" THEN doc_linhas.Quantity ELSE 0 END)) as TotalQuantitySold'
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('vps_cacimbo_api_erp.docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->where('vps_cacimbo_api_erp.docs.SourceDocuments','SalesInvoices')
            ->where('vps_cacimbo_api_erp.docs.CompanyID', $nif)
            ->where('vps_cacimbo_api_erp.docs.sync_status', 0)
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('vps_cacimbo_api_erp.docs.InvoiceDate', Carbon::today());
            })
            ->groupBy('doc_linhas.ProductCode', 'doc_linhas.ProductDescription')
            ->orderByDesc('TotalQuantitySold','desc')
            ->limit(10)
            ->get();


        $topProductsTotal = DocLinha::query()
            ->join('vps_cacimbo_api_erp.docs', 'vps_cacimbo_api_erp.docs.invoiceId', '=', 'doc_linhas.invoiceId')
            ->selectRaw('
            doc_linhas.ProductCode,
            doc_linhas.ProductDescription,
            (SUM(CASE WHEN vps_cacimbo_api_erp.docs.InvoiceType <> "NC" AND vps_cacimbo_api_erp.docs.InvoiceType <> "A" THEN doc_linhas.Quantity ELSE 0 END)
                - SUM(CASE WHEN vps_cacimbo_api_erp.docs.InvoiceType = "NC" AND vps_cacimbo_api_erp.docs.InvoiceType <> "A" THEN doc_linhas.Quantity ELSE 0 END)) as TotalQuantitySold,
                SUM((doc_linhas.CreditAmount + (doc_linhas.CreditAmount * doc_linhas.TaxPercentage / 100))) AS TotalSalesAmount'
                
            )
            ->when(isset($request->start_date) && isset($request->end_date), function ($q) use ($request) {
                $q->whereBetween('vps_cacimbo_api_erp.docs.InvoiceDate', [$request->start_date, $request->end_date]);
            })
            ->where('vps_cacimbo_api_erp.docs.SourceDocuments','SalesInvoices')
            ->where('vps_cacimbo_api_erp.docs.CompanyID', $nif)
            ->where('vps_cacimbo_api_erp.docs.sync_status', 0)
            ->when(!isset($request->start_date) || !isset($request->end_date), function ($q) use ($request) {
                $q->whereDate('vps_cacimbo_api_erp.docs.InvoiceDate', Carbon::today());
            })
            ->groupBy('doc_linhas.ProductCode', 'doc_linhas.ProductDescription')
            ->orderByDesc('TotalSalesAmount','desc')
            ->limit(10)
            ->get();

        $docsMovimentos = DocPayment::query()
            ->selectRaw('PaymentMechanism, PaymentMechanismDescription, SUM(CreditAmount)  
            as entrada, SUM(DebitAmount) as saida')
            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                $q->whereBetween('PaymentDate',[$request->start_date,$request->end_date]);
            })
            ->where('CompanyID', $nif)
            ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                $q->whereDate('PaymentDate', Carbon::today());
            })
            ->groupBy('PaymentMechanism')
            ->get();
            
        $result = [
            'totais' => $docs[0],
            'TopClients' => $topClients->toArray(),
            'TopArtigos' =>  $topProducts->toArray(),
            'TopArtigosAmount' =>  $topProductsTotal->toArray(),
            'SeriesDetails' => $seriesDetails->toArray(),
            'Contas' => $docsContas->toArray(),
            'mecanismos' => $docsMovimentos->toArray()            
        ];

        return response()->json($result, 200);
    }



}