<?php

namespace App\Http\Services;

use App\Core\Util;
use App\Http\Services\DocEntidadeService;
use App\Http\Services\OnlinePaymentService;
use App\Http\Services\UsersOnlineStoreService;
use App\Models\DocEntidade;
use App\Models\DocEntidadesSaldos;
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


class DocService
{
    public static function addFieldInArrayLines($lines, $doc_number)
    {   
        $mapped = [];
        $i = 1;
        foreach ($lines as $line) {
            $line['InvoiceId'] = $doc_number;
            $line['InvoiceNo'] = $doc_number;
            $line['LineNumber'] = $doc_number.'-line-temp-'. $i++;
            $mappedArray = array_push($mapped, $line);
        }
        return $mapped;
    }

    public static function insertDocAndLines($request)
    {
        $doc_number = DocNumber::create([
            'CompanyID' => $request['empresa_nif'],
            'option_after_sync' => $request->option_after_sync,
            'doc_user' => $request->doc_user
        ]);
        $customerId;
        $customer = DocEntidade::find($request["CustomerID"]);
        if($customer!=null){
            $customerId = $customer->CustomerID;
        }else{
            $customerId = DocEntidade::where("temp_CustomerID")->first()->CustomerID;
        }
        $doc_number->update(['temp_doc_number' => $request['empresa_nif'] . '-temp-' . $doc_number->id]);
        $data['InvoiceNo'] = $doc_number->temp_doc_number;
        $data['InvoiceId'] = $doc_number->temp_doc_number;
        $data['InvoiceStatusDate'] = Carbon::now();
        $data['SourceBilling'] = "P";
        $data['HASH'] = Str::random(4);
        $data['Period'] = Carbon::now()->month;
        $data['InvoiceDate'] = Carbon::now();
        $data['InvoiceType'] = $request["InvoiceType"];
        $data['InvoiceTypeSerie'] = $request["InvoiceTypeSerie"];
        $data['SystemEntryDate'] = Carbon::now();
        $data['CustomerID'] = $customerId;
        $data['CompanyID'] = $request['empresa_nif'];
        $data['AddressDetail'] = 'Lobito';
        $data['City'] = 'Lobito';
        $data['sync_status'] = 201;
        $data['Country'] = 'Angola';
        if (isset($request->InvoiceType) && $request->InvoiceType == "PP") {
            $data['SourceDocuments'] = "WorkingDocuments";
        }else{
            $data['SourceDocuments'] = "SalesInvoices";
        }
        $data['TaxPayable'] = $request->TaxPayable;
        $data['NetTotal'] = $request->NetTotal;
        $data['GrossTotal'] = $request->GrossTotal;
        $lines = $request['lines'];
        $doc = Docs::create($data);
        $arrayLines = DocService::addFieldInArrayLines($lines, $doc->InvoiceNo);
        $doc->docLinhas()->createMany($arrayLines);
        $doc->load("docLinhas");
        try {
            $socketData =["command" => "message",
                "message" =>[
                "company_id" => $data['CompanyID'],
                "type" => "sync_exe",
                "method" => "execute",
                "info" => "",
                "time" => Carbon::now()
                ],
                "channel" => $data['CompanyID']
            ];
            Util::sendMessageWebSocket($socketData);
            return $doc;

        } catch (\Throwable $th) {
           return $doc;
        }
        return $doc;
    }

    public static function convertUserToCostumer($user, $request) {
        $data['nif']  = $user->nif ;
        $data['nome'] = $user->name;
        $data['empresa_nif'] = $request->empresa_nif;
        $data['contacto_nome'] = $user->name;
        $data['morada'] = $request->street;
        $data['localidade'] = $request->city;
        $data['pais'] = $request->country;
        $data['email'] = $user->email;
        $data['telefone'] = $request->telefone;
        return $data;
    }

    public static function insertDocAndLinesForOnlineShop($request)
    {
        $doc_number = DocNumber::create([
            'CompanyID' => $request['empresa_nif'],
            'option_after_sync' => $request->option_after_sync,
            'doc_user' => $request->doc_user
        ]);
        $user = User::find($request->user_id);           
        $dataForCostumer = DocService::convertUserToCostumer($user, $request);
        $customerCreated = DocEntidadeService::insertDocEntidadeApi($dataForCostumer);

        $doc_number->update(['temp_doc_number' => $request['empresa_nif'] . '-temp-' . $doc_number->id]);
        $data['InvoiceNo'] = $doc_number->temp_doc_number;
        $data['InvoiceId'] = $doc_number->temp_doc_number;
        $data['InvoiceStatusDate'] = Carbon::now();
        $data['SourceBilling'] = "P";
        $data['HASH'] = "temp";
        $data['Period'] = Carbon::now()->month;
        $data['InvoiceDate'] = Carbon::now();
        $data['InvoiceType'] = $request["InvoiceType"];
        $data['InvoiceTypeSerie'] = $request["InvoiceTypeSerie"];
        $data['SystemEntryDate'] = Carbon::now();
        $data['CustomerID'] = $customerCreated->CustomerID;
        $data['CompanyID'] = $request['empresa_nif'];
        $data['AddressDetail'] = $customerCreated->morada;
        $data['City'] = $customerCreated->localidade;
        $data['Country'] = $customerCreated->pais;
        $data['sync_status'] = 201;
        $data['doc_status'] = "Pendente";
        $data['OnlineStorePO'] = 1;
        if (isset($request->InvoiceType) && ($request->InvoiceType == "PP" || $request->InvoiceType == "NE")) {
            $data['SourceDocuments'] = "WorkingDocuments";
        }else{
            $data['SourceDocuments'] = "SalesInvoices";
        }
        $data['TaxPayable'] = $request->TaxPayable;
        $data['NetTotal'] = $request->NetTotal;
        $data['GrossTotal'] = $request->GrossTotal;
        $lines = $request['lines'];
        $doc = Docs::create($data);
        $arrayLines = DocService::addFieldInArrayLines($lines, $doc->InvoiceNo);
        $doc->docLinhas()->createMany($arrayLines);
        $doc->load("docLinhas");
        if ($doc) {
            $onlinePaymentService = new OnlinePaymentService();
            $onlinePaymentService->payForOnlineDoc($request, $doc);
            $doc->docStatuses()->create([
                "status_id"=>101,
                "status_date" => Carbon::now(),
                "last_status_id" => 1
            ]);
        }
        try {
            $socketData =["command" => "message",
                "message" =>[
                "company_id" => $data['CompanyID'],
                "type" => "sync_exe",
                "method" => "execute",
                "info" => "",
                "time" => Carbon::now()
                ],
                "channel" => $data['CompanyID']
            ];
            Util::sendMessageWebSocket($socketData);
            return $doc;

        } catch (\Throwable $th) {
           return $doc;
        }
        return $doc;
    }

    public static function insertDoc($request){
        $data['invoice_no'] = $request->invoice_no;
        $data['invoice_id'] = $request->invoice_id;
        $data['status'] = $request->status;
        $data['invoice_status_date'] = $request->invoice_status_date;
        $data['source_billing'] = $request->source_billing;
        $data['hash'] = $request->hash;
        $data['period'] = $request->period;
        $data['invoice_date'] = $request->invoice_date;
        $data['invoice_type'] = $request->invoice_type;
        $data['system_entry_date'] = $request->system_entry_date;
        $data['customer_id'] = $request->customer_id;
        $data['customer_com_id'] = $request->customer_com_id;
        $data['company_id'] = $request->company_id;
        $data['adress_detail'] = $request->adress_detail;
        $data['city'] = $request->city;
        $data['country'] = $request->country;
        $data['tax_payable'] = $request->tax_payable;
        $data['net_total'] = $request->net_total;
        $data['gross_total'] = $request->gross_total;
        $data['irt_with_holding_tax_type'] = $request->irt_with_holding_tax_type;
        $data['irt_with_holding_tax_description'] = $request->irt_with_holding_tax_description;
        $data['irt_with_holding_tax_amount'] = $request->irt_with_holding_tax_amount;
        $data['is_with_holding_tax_type'] = $request->is_with_holding_tax_type;
        $data['is_with_holding_tax_description'] = $request->is_with_holding_tax_description;
        $data['is_with_holding_tax_amount'] = $request->is_with_holding_tax_amount;
        $data['id_veiculo'] = $request->id_veiculo;
        $data['id_vendedor'] = $request->id_vendedor;
        $data['id_user'] = $request->id_user;
        $data['id_api_user'] = $request->id_api_user;
        $data['doc_description'] = $request->doc_description;
        $data['doc_obs'] = $request->doc_obs;
        $data['doc_ref'] = $request->doc_ref;
        $data['id_retorno_api'] = $request->id_retorno_api;
        $doc = Docs::create($data);
        return $doc;
    }

    public static function analyticsForCharts($request,$nif)
    {
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date) : Carbon::now();
        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date) : $end_date->copy()->subMonths(6);
    
        // Garante no máximo 6 meses de diferença
        if ($start_date->diffInMonths($end_date) > 6) {
            $start_date = $end_date->copy()->subMonths(6);
        }
    
        $date_diff_days = $start_date->diffInDays($end_date);
        $date_diff_months = $start_date->diffInMonths($end_date);
    

      if ($date_diff_days == 0 && $date_diff_months == 0) {
        $docs = Docs::query()
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral, 
                CASE    WHEN TIME(created_at) BETWEEN "00:00:00" AND "05:59:59" THEN "Madrugada"
                        WHEN TIME(created_at) BETWEEN "06:00:00" AND "11:59:59" THEN "Manhã"
                        WHEN TIME(created_at) BETWEEN "12:00:00" AND "17:59:59" THEN "Tarde"
                        WHEN TIME(created_at) BETWEEN "18:00:00" AND "23:59:59" THEN "Noite"
                    END as hour')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.SourceDocuments','SalesInvoices')
                ->where('CompanyID', $nif)
                ->where('sync_status', 0)
                ->whereBetween('docs.InvoiceDate', [$start_date, $end_date])
                ->groupByRaw("
                CASE WHEN TIME(created_at) BETWEEN '00:00:00' AND '05:59:59' THEN 'Madrugada'
                    WHEN TIME(created_at) BETWEEN '06:00:00' AND '11:59:59' THEN 'Manhã'
                    WHEN TIME(created_at) BETWEEN '12:00:00' AND '17:59:59' THEN 'Tarde'
                    WHEN TIME(created_at) BETWEEN '18:00:00' AND '23:59:59' THEN 'Noite'
                END")
                ->get();
            $docsArray['0']['values'] = $docs->toArray();
            $docsArray['0']['Visualizacao'] = "Horas";
            return $docsArray;
      }

      if ($date_diff_days > 0 && $date_diff_months == 0) {
        $docs = Docs::query()
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral, InvoiceDate as day')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.SourceDocuments','SalesInvoices')
                ->where('CompanyID', $nif)
                ->where('sync_status', 0)
                ->whereBetween('docs.InvoiceDate', [$start_date, $end_date])
                ->orderByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate), Day(docs.InvoiceDate)')
                ->groupByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate), Day(docs.InvoiceDate)')
                ->get();
                $docsArray['0']['values'] = $docs->toArray();
                $docsArray['0']['Visualizacao'] = "Dias";
                return $docsArray;
      }
      if ($date_diff_days > 0 && $date_diff_months > 0) {
        $docs = Docs::query()
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral, Month(InvoiceDate) as month, Year(InvoiceDate) as year')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.SourceDocuments','SalesInvoices')
                ->where('CompanyID', $nif)
                ->where('sync_status', 0)
                ->whereBetween('docs.InvoiceDate', [$start_date, $end_date])
                ->orderByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate)')
                ->groupByRaw('Year(docs.InvoiceDate), Month(docs.InvoiceDate)')
                ->get();
                $docsArray['0']['values'] = $docs->toArray();
                $docsArray['0']['Visualizacao'] = "Mes";
                return $docsArray;
      }
       
    }

    public static function insertDocApi($request)
    {
        $customerId;
        $customer = DocEntidade::find($request["CustomerID"]);
        if($customer!=null){
            $customerId = $customer->CustomerID;
        }else{
            $customerId = DocEntidade::where("temp_CustomerID")->first()->CustomerID;
        }
        $doc_number = DocNumber::create([
            'CompanyID' => $request['empresa_nif']
        ]);
        $doc_number->update(['temp_doc_number' => $request['empresa_nif'] . '-temp-' . $doc_number->id]);
        $data['InvoiceNo'] = $doc_number->temp_doc_number;
        $data['InvoiceId'] = $doc_number->temp_doc_number;
        $data['InvoiceStatusDate'] = Carbon::now();
        $data['SourceBilling'] = "P";
        $data['HASH'] = Str::random(4);
        $data['Period'] = '11';
        $data['InvoiceDate'] = Carbon::now();
        $data['InvoiceType'] = $request["InvoiceType"];
        $data['SystemEntryDate'] = Carbon::now();
        $data['CustomerID'] = $customerId;
        $data['CompanyID'] = $request['empresa_nif'];
        $data['AddressDetail'] = 'Lobito';
        $data['City'] = 'Lobito';
        $data['Country'] = 'Angola';
        $data['SourceDocuments'] = $request["SourceDocuments"];
        $data['TaxPayable'] = 0.00;
        $data['NetTotal'] = $request['NetTotal'];
        $data['GrossTotal'] = $request['GrossTotal'];
        $doc = Docs::create($data);
        try {
            $socketData =[
                "company_id" => $data['CompanyID'],
                "type" => "sync_exe",
                "method" => "execute",
                "info" => "",
                "time" => Carbon::now()
            ];
            Util::sendMessageWebSocket($socketData);
            return $doc;

        } catch (\Throwable $th) {
           return $doc;
        }
        return $doc;
    }

    public static function insertDocForOnlineShopApi($request)
    {
        //dd($request);
        $user = User::find($request->user_id);           
        $dataForCostumer = DocService::convertUserToCostumer($user,$request['empresa_nif'], $request);
        $customerCreated = DocEntidadeService::insertDocEntidadeApi($dataForCostumer);
        $doc_number = DocNumber::create([
            'CompanyID' => $request['empresa_nif']
        ]);
        $doc_number->update(['temp_doc_number' => $request['empresa_nif'] . '-temp-' . $doc_number->id]);
        $data['InvoiceNo'] = $doc_number->temp_doc_number;
        $data['InvoiceId'] = $doc_number->temp_doc_number;
        $data['InvoiceStatusDate'] = Carbon::now();
        $data['SourceBilling'] = "P";
        $data['HASH'] = "temp";
        $data['Period'] = Carbon::now()->month;
        $data['InvoiceDate'] = Carbon::now();
        $data['InvoiceType'] = $request["InvoiceType"];
        $data['SystemEntryDate'] = Carbon::now();
        $data['CustomerID'] = $customerCreated->CustomerID;
        $data['CompanyID'] = $request['empresa_nif'];
        $data['AddressDetail'] = $customerCreated->morada;
        $data['City'] = $customerCreated->localidade;
        $data['Country'] = $customerCreated->pais;
        $data['SourceDocuments'] = $request["SourceDocuments"];
        $data['TaxPayable'] = 0.00;
        $data['NetTotal'] = $request['NetTotal'];
        $data['GrossTotal'] = $request['GrossTotal'];
        $doc = Docs::create($data);
        return $doc;
    }
    
    public static function insertDocBySaft($request, $nif){
        $data['InvoiceNo'] = $request["InvoiceNo"];
        $data['InvoiceId'] = $request['empresa_nif']."-" .$request["InvoiceNo"];
        $data['InvoiceStatusDate'] = $request["DocumentStatus"]["InvoiceStatusDate"];
        $data['SourceBilling'] = $request["DocumentStatus"]["SourceBilling"];
        $data['HASH'] = $request["HASH"];
        $data['Period'] = $request["Period"];
        $data['InvoiceDate'] = $request["InvoiceDate"];
        $data['InvoiceType'] = $request["InvoiceType"];
        $data['SystemEntryDate'] = $request["SystemEntryDate"];
        $data['CustomerID'] = $nif."-".$request["CustomerID"];
        $data['CompanyID'] = $nif;
        $data['AddressDetail'] = $request0['ShipTo']['Address']["AddressDetail"];
        $data['City'] = $request['ShipTo']['Address']["City"];
        $data['Country'] = $request['ShipTo']['Address']["Country"];
        $data['TaxPayable'] = $request["DocumentTotals"]['TaxPayable'];
        $data['NetTotal'] = $request["DocumentTotals"]['NetTotal'];
        $data['GrossTotal'] = $request["DocumentTotals"]['GrossTotal'];
        $data['DocRef'] = $request["SourceID"];
        $doc = Docs::UpdateOrCreate(["InvoiceId" => $data['InvoiceId']], $data);
        return $doc;
    }

    public static function getAllDocs(){
        $docs = Docs::all();
        return $docs;
    }

    public static function getDocsPurchases($request, $nif){
        $docs = Docs::query()
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC"  AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral,SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)  
                as total_debito, SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_credito, SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END) as total_iva')
                ->where('doc_entidades.CustomerTaxID', str_replace('-1', ' ', $nif))
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->where('docs.SourceDocuments','SalesInvoices')
                ->get();
        $docsTotalCredit = Docs::query()
            ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
            ->selectRaw("SUM(CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.GrossTotal ELSE 0 END) as amount, InvoiceTypeSerie as name, IF(STRCMP(docs.InvoiceType,'NC'), 'Debito','Credito') as movimentType, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.TaxPayable ELSE 0 END as taxPayable, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.Settlement ELSE 0 END as settlement")
            ->where('doc_entidades.CustomerTaxID',str_replace('-1', ' ', $nif))
            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
            })
            ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->where('docs.SourceDocuments','SalesInvoices')
            ->groupBy("InvoiceTypeSerie")
            ->get();
            $docsTotalCreditArray = ($docs['0']['total_geral']) ? $docs->toArray() : [];
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["key"] = "Compras"  : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["description"] = "Compras"  : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["documents"] =  $docsTotalCredit : [] ;
        return $docsTotalCreditArray;
    }
    
    public static function getDocsSales($request, $nif){
        $docs = Docs::query()
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral, SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)  
                as total_credito, SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_debito, SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END) as total_iva')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.SourceDocuments','SalesInvoices')
                ->where('docs.CompanyID', $nif)
                ->where('sync_status', 0)
                ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->get();
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
            $docsTotalCreditArray = ($docs['0']['total_geral']) ? $docs->toArray() : [];
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["key"] = "Vendas" : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["description"] = "Vendas" : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["documents"] = $docsTotalCredit : [];
        return $docsTotalCreditArray;
    }

    public static function getDocsSalesDraft($request, $nif){
        $docs = Docs::query()
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->join('doc_numbers', 'docs.InvoiceId', '=','doc_numbers.temp_doc_number')
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral, SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)  
                as total_credito, SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_debito, SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END) as total_iva')
                /*->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[Carbon::createFromDate($request->start_date)->firstOfYear(),Carbon::createFromDate($request->end_date)->lastOfYear()]);
                })*/
                ->whereIn('docs.SourceDocuments',['SalesInvoices','WorkingDocuments'])
                ->where('docs.CompanyID', $nif)
                /*->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[Carbon::today()->firstOfYear(),Carbon::today()->lastOfYear()]);
                })*/
                ->get();
        $docsTotalCredit = Docs::query()
            ->join('doc_numbers', 'docs.InvoiceId', '=','doc_numbers.temp_doc_number')
            ->selectRaw("SUM(CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.GrossTotal ELSE 0 END) as amount, InvoiceTypeSerie as name, IF(STRCMP(docs.InvoiceType,'RC') AND STRCMP(docs.InvoiceType,'NC'), 'Debito','Credito') as movimentType, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.TaxPayable ELSE 0 END as taxPayable, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.Settlement ELSE 0 END as settlement")
            /*->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                $q->whereBetween('docs.InvoiceDate',[Carbon::createFromDate($request->start_date)->firstOfYear(),Carbon::createFromDate($request->end_date)->lastOfYear()]);
            })
            ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                $q->whereBetween('docs.InvoiceDate',[Carbon::today()->firstOfYear(),Carbon::today()->lastOfYear()]);
            })*/
            ->where('docs.CompanyID', $nif)
            ->groupBy("InvoiceTypeSerie")
            ->get();
            $docsTotalCreditArray = $docs->toArray();
            $docsTotalCreditArray['0']["key"] = "Rascunhos" ;
            $docsTotalCreditArray['0']["description"] =( $docs['0']['total_geral']) ? "Rascunhos" : "Novo Documento" ;
            $docsTotalCreditArray['0']["documents"] = $docsTotalCredit ;
        return $docsTotalCreditArray;
    }

    public static function getDocsPayments($request, $nif){
        $docs = DocPayment::query()
                ->selectRaw('(SUM(CreditAmount) - SUM(DebitAmount)) as total_geral, SUM(CreditAmount)  
                as total_credito, SUM(DebitAmount) as total_debito, 0 as total_iva')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('PaymentDate',[$request->start_date,$request->end_date]);
                })
                ->where('CompanyID', $nif)
                ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereDate('PaymentDate', Carbon::today());
                })
                ->get();
            $docsContas = DocPayment::query()
            ->selectRaw('AccountBank,AccountNum ,SUM(CreditAmount)  
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
            $docsTotalCreditArray = ($docs['0']['total_geral'])  ? $docs->toArray() : [];
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["key"] = "Caixa" : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["description"] = "Caixa" : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']['documents']=[] : [];
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["movimentos"] = $docsMovimentos : [];
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["contas"] = $docsContas : [] ;
        return $docsTotalCreditArray;
    }

    public static function getDocsWorkingDocuments($request, $nif){
        $docs = Docs::query()
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('(SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)
                - SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)) as total_geral, SUM(CASE WHEN docs.InvoiceType <> "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END)  
                as total_credito, SUM(CASE WHEN docs.InvoiceType = "NC" AND docs.InvoiceStatus <> "A" THEN docs.GrossTotal ELSE 0 END) as total_debito, SUM(CASE WHEN docs.InvoiceStatus <> "A" THEN docs.TaxPayable ELSE 0 END) as total_iva')
                ->where('docs.SourceDocuments', 'WorkingDocuments')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.CompanyID', $nif)
                ->where('sync_status', 0)
                ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->get();
        $docsTotalCredit = Docs::query()
            ->selectRaw("SUM(CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.GrossTotal ELSE 0 END) as amount, InvoiceTypeSerie as name, IF(STRCMP(docs.InvoiceType,'NC'), 'Credito','Debito') as movimentType, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.TaxPayable ELSE 0 END as taxPayable, CASE WHEN docs.InvoiceStatus <> 'A' THEN docs.Settlement ELSE 0 END as settlement")
            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
            })
            ->where('docs.SourceDocuments', 'WorkingDocuments')
            ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                $q->whereDate('docs.InvoiceDate', Carbon::today());
            })
            ->where('docs.CompanyID', $nif)
            ->where('sync_status', 0)
            ->groupBy("InvoiceTypeSerie")
            ->get();
            $docsTotalCreditArray = ($docs['0']['total_geral']) ? $docs->toArray() : [];
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["key"] = "Outros" : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["description"] = "Outros" : '';
            ($docs['0']['total_geral']) ? $docsTotalCreditArray['0']["documents"] = $docsTotalCredit : [];
        return $docsTotalCreditArray;
    }

    public static function getCustomerBalances($request, $nif){
        $docsBalance = Docs::query()
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('doc_entidades.CustomerID, doc_entidades.CustomerTaxID, doc_entidades.CompanyName, doc_entidades.Telefone, SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as debito, 
                            SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as credito, 
                            (SUM(CASE  when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) - SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END)) as saldo')
                ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                ->where('docs.CompanyID', $nif)
                ->groupBy(["docs.CompanyID","docs.CustomerID"])
                ->orderByRaw("doc_entidades.CompanyName ")
                ->paginate(20);
        return $docsBalance;
    }  

    public static function getCustomerBalancesV2($request, $nif){
        $docsBalance = Docs::query()
            ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
            ->selectRaw('doc_entidades.CustomerID, doc_entidades.CustomerTaxID, doc_entidades.CompanyName, doc_entidades.Telefone, 
                        SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as debito, 
                        SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as credito, 
                        (SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) - 
                         SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END)) as saldo')
            ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
            ->where('docs.CompanyID', $nif)
            ->groupBy(["docs.CompanyID","docs.CustomerID"])
            ->orderByRaw("doc_entidades.CompanyName ")
            ->get();

            $docsBalanceDetails = Docs::query()
            ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
            ->selectRaw('doc_entidades.CustomerID, doc_entidades.CustomerTaxID, doc_entidades.CompanyName, doc_entidades.Telefone, 
                        SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as debito, 
                        SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as credito, 
                        (SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) - 
                         SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END)) as saldo')
            ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
            ->where('docs.CompanyID', $nif)
            ->groupBy(["docs.CompanyID","docs.CustomerID"])
            ->orderByRaw("doc_entidades.CompanyName ")
            ->paginate(20);
        
        $totalDebito = $docsBalance->sum('debito');
        $totalCredito = $docsBalance->sum('credito');
        $totalSaldo = $docsBalance->sum('saldo');
        $docsTotalCreditArray["0"]["total_debito"] = $totalDebito;
        $docsTotalCreditArray["0"]["total_credito"] = $totalCredito;
        $docsTotalCreditArray["0"]["total_saldo"] =  $totalSaldo;
        $docsTotalCreditArray["0"]["documents"]=$docsBalanceDetails;
        return $docsTotalCreditArray;
    }

    public static function getCustomerCurrentAccount($request, $nif, $customerId){
        $docsBalance = Docs::query()
                            ->leftJoin('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                            ->selectRaw('COALESCE(SUM(CASE when InvoiceType in ("FT","SI","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END), 0) as inicial_debito, 
                                COALESCE(SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END), 0) as inicial_credito, 
                                COALESCE(SUM(CASE when InvoiceType in ("FT","ND","SI","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END), 0) - COALESCE(SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END), 0) as inicial_saldo')
                            ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                            ->where('docs.CompanyID', $nif)
                            ->where('docs.CustomerID', $customerId)
                            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                                $q->where('docs.InvoiceDate','<', $request->start_date);
                            })
                            ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                                $q->where('docs.InvoiceDate','<', Carbon::today()->firstOfYear());
                            })
                            ->groupBy(["docs.CompanyID","docs.CustomerID"])
                            ->orderByRaw("InvoiceDate, docs.CustomerID")
                            ->get();          
        $queryCurrentAccount = Docs::query()
                ->leftJoin('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as final_debito, 
                            SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as final_credito, 
                            (SUM(CASE  when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) - SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END)) as final_saldo')
                ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate', [Carbon::today()->firstOfYear(), Carbon::today()]);
                })
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.CompanyID', $nif)
                ->where('docs.CustomerID', $customerId)
                ->groupBy(["docs.CompanyID","docs.CustomerID"])
                ->orderByRaw("InvoiceDate, docs.CustomerID")
                ->get();

                $balances = $docsBalance->toArray();
                if (empty($balances)) {
                    $balances = [[
                        'inicial_debito' => 0,
                        'inicial_credito' => 0,
                        'inicial_saldo' => 0,
                    ]];
                }
                
                $docsCurrentAccount = $balances;
                $docsCurrentAccount['0']['data_inicial']= (!isset($request->start_date) || !isset($request->end_date)) ? Carbon::today()->firstOfYear() : $request->start_date;
                $docsCurrentAccount['0']['data_final'] = (!isset($request->start_date) || !isset($request->end_date)) ? Carbon::today() : $request->end_date;
                $docsCurrentAccount['0']['final_debito'] = empty($queryCurrentAccount->toArray()) ? 0 : $queryCurrentAccount['0']->final_debito;
                $docsCurrentAccount['0']['final_credito'] = empty($queryCurrentAccount->toArray()) ? 0 : $queryCurrentAccount['0']->final_credito;
                $docsCurrentAccount['0']['final_saldo'] = empty($queryCurrentAccount->toArray()) ? 0 : $queryCurrentAccount['0']->final_saldo;
                $docsCurrentAccount['0']["key"] = "conta_corrente";
                $docsCurrentAccount['0']["description"] = "conta_corrente";
                $docsCurrentAccount['0']["documents"] = [];

        return $docsCurrentAccount;
    }

    public static function getCustomerCurrentAccountDetails($request, $nif, $customerId){
        $queryCurrentAccountDocsLines = Docs::query()
                ->with('customer')
                ->leftJoin('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('InvoiceId, InvoiceNo, InvoiceDate, InvoiceType, InvoiceTypeSerie, 
                            DocDescription, DocRef, docs.CompanyID, docs.CustomerID, InvoiceStatus,
                            CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END as debito, 
                            CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END as credito, 
                            (CASE  when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END - CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as saldo')
                ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate', [Carbon::today()->firstOfYear(), Carbon::today()]);
                   // $q->where('docs.InvoiceDate', '<', Carbon::today());
                })
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.CompanyID', $nif)
                ->where('docs.CustomerID', $customerId)
                ->orderByRaw("InvoiceDate, InvoiceNo")
                ->paginate(50);
                return $queryCurrentAccountDocsLines;

    }

    public static function getDocsSalesDetailsWithLines($request, $nif){
        $docs = Docs::query()
                ->with(['docLinhas','customer','company'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->select('docs.*','doc_entidades.CompanyName as customer_name')
                ->when(isset($request->start_date) && isset($request->end_date) , function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.CompanyID', $nif)
                ->where('sync_status', 0)
                ->when((!isset($request->start_date) || !isset($request->end_date)) && !isset($request->invoice_id), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->when(isset($request->doc_type), function($q) use($request){
                    $q->where('docs.InvoiceTypeSerie', $request->doc_type);
                })
                ->when(isset($request->invoice_id), function($q) use($request){
                    $q->where('InvoiceId', $request->invoice_id);
                })
                ->whereIn('docs.SourceDocuments',['SalesInvoices','Payments'])
                ->orderByRaw("InvoiceTypeSerie, InvoiceDate, InvoiceNo")
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->paginate(50);
        return $docs;
    }

    public static function getDocsDraftsDetailsWithLines($request, $nif){
        $docs = Docs::query()
                ->with(['docLinhas','customer','company'])
                ->join('doc_numbers', 'docs.InvoiceId', '=','doc_numbers.temp_doc_number')
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->select('docs.*','doc_entidades.CompanyName as customer_name','doc_numbers.sync_status_description')
                /*->when(isset($request->start_date) && isset($request->end_date) , function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[Carbon::createFromDate($request->start_date)->firstOfYear(),Carbon::createFromDate($request->end_date)->lastOfYear()]);
                })*/
                ->where('docs.CompanyID', $nif)
                /*->when((!isset($request->start_date) || !isset($request->end_date)) && !isset($request->invoice_id), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[Carbon::today()->firstOfYear(),Carbon::today()->lastOfYear()]);
                })*/
                ->when(isset($request->doc_type), function($q) use($request){
                    $q->where('docs.InvoiceTypeSerie', $request->doc_type);
                })
                ->when(isset($request->invoice_id), function($q) use($request){
                    $q->where('docs.InvoiceId', $request->invoice_id);
                })
                //->whereIn('docs.SourceDocuments',['SalesInvoices','WorkingDocuments'])
                ->orderByRaw("InvoiceTypeSerie, InvoiceDate, InvoiceNo")
                ->groupBy(["docs.InvoiceTypeSerie", "docs.InvoiceDate", "docs.InvoiceNo"])
                ->paginate(50);
        return $docs;
    }

    public static function getDocsWorkingDetailsWithLines($request, $nif){
        $docs = Docs::query()
                ->with(['docLinhas','customer','company'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->select('docs.*','doc_entidades.CompanyName as customer_name')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.CompanyID', $nif)
                ->where('sync_status', 0)
                ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->when(isset($request->doc_type), function($q) use($request){
                    $q->where('docs.InvoiceTypeSerie', $request->doc_type);
                })
                ->when(isset($request->invoice_id), function($q) use($request){
                    $q->where('docs.InvoiceId', $request->invoice_id);
                })
                ->where('docs.SourceDocuments','WorkingDocuments')
                ->orderByRaw("InvoiceTypeSerie, InvoiceDate, InvoiceNo")
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->paginate(50);
        return $docs;
    }

    public static function getDocsPurchasesDetailsWithLines($request, $nif){
        $docs = Docs::query()
                ->with(['docLinhas','customer','company'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->join('doc_empresas','docs.CompanyID', '=', 'doc_empresas.CompanyID')
                ->select('docs.*','doc_empresas.CompanyName as customer_name')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('doc_entidades.CustomerTaxID',str_replace('-1', ' ', $nif))
                ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->when(isset($request->doc_type), function($q) use($request){
                    $q->where('docs.InvoiceTypeSerie', $request->doc_type);
                })
                ->when(isset($request->invoice_id), function($q) use($request){
                    $q->where('docs.InvoiceId', $request->invoice_id);
                })
                ->where('docs.SourceDocuments','SalesInvoices')
                ->where('sync_status', 0)
                ->orderByRaw("InvoiceTypeSerie, InvoiceDate, InvoiceNo")
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->paginate(50);
        return $docs;
    }

    public static function getDocsDraftsOrderDetailsWithLines($request, $nif){
        $users_expositores_ids = UsersOnlineStoreService::getOnlineStoresByUserNif($nif);
        $docs = Docs::query()
                ->with(['docLinhas','customer','company', 'docStatuses'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->join('doc_empresas','docs.CompanyID', '=', 'doc_empresas.CompanyID')
                ->join('online_stores', 'docs.CompanyID', '=', 'online_stores.CompanyID')
                ->select('docs.*','online_stores.StoreName as expositor_name','doc_entidades.CompanyName as customer_name')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where(function($q) use($nif, $users_expositores_ids){
                    $q->where('doc_entidades.CustomerTaxID',str_replace('-1', ' ', $nif))
                     ->orWhereIn('online_stores.id',$users_expositores_ids);
                })
                //->where('doc_entidades.CustomerTaxID',str_replace('-1', ' ', $nif))
                ->when(isset($request->invoice_id), function($q) use($request){
                    $q->where('docs.InvoiceId', $request->invoice_id);
                })
                ->where('docs.OnlineStorePO', 1)
                ->where("doc_status","<>", "concluído")
                ->orderBy("created_at", 'DESC')
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->paginate(50);

                //Adicionar o campo "doc_status_code" em cada documento
                foreach ($docs as $doc) {
                    $doc["doc_status_code"] = ($doc->docStatuses->last()) ? $doc->docStatuses->last()->status_id : 0;
                }
        return $docs;
    }


    public static function getDocsDraftOrderDetailsWithLinesExpositores($request, $id_user){
        $users_expositores_ids = UsersOnlineStoreService::getOnlineStoresByUserId($id_user);
        $docs = Docs::query()
                ->with(['docLinhas','customer','company', 'docStatuses'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->join('doc_empresas','docs.CompanyID', '=', 'doc_empresas.CompanyID')
                ->join('online_stores', 'docs.CompanyID', '=', 'online_stores.CompanyID')
                ->select('docs.*','online_stores.StoreName as expositor_name','doc_entidades.CompanyName as customer_name')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->whereIn('online_stores.id',$users_expositores_ids)
                ->when(isset($request->invoice_id), function($q) use($request){
                    $q->where('docs.InvoiceId', $request->invoice_id);
                })
                ->where('docs.OnlineStorePO', 1)
                ->where("doc_status","<>", "concluído")
                ->orderBy("created_at", 'DESC')
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->paginate(50);

                //Adicionar o campo "doc_status_code" em cada documento
                foreach ($docs as $doc) {
                    $doc["doc_status_code"] = ($doc->docStatuses->last()) ? $doc->docStatuses->last()->status_id : 0;
                }
        return $docs;
    }

    public static function getDocsSalesDetailsWithPayments($request, $nif){
        $docs = Docs::query()
                ->with(['docPayments'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->select('docs.*','doc_entidades.CompanyName as customer_name')
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->whereIn('docs.CompanyID', function($q) use($nif){
                    $q->select('CompanyID')
                        ->from('doc_empresas')
                        ->where('TaxRegistrationNumber', $nif);
                })
                ->when(!isset($request->start_date) ||  !isset($request->end_date), function($q) use($request){
                    $q->whereDate('docs.InvoiceDate', Carbon::today());
                })
                ->where('docs.InvoiceType','<>','RC')
                ->where('sync_status', 0)
                ->orderByRaw("InvoiceTypeSerie, InvoiceDate, InvoiceNo")
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->get();
        return $docs;
    }

    public static function deleteDocDraft($invoiceId)
    {
        $doc = Docs::where('InvoiceId',$invoiceId)->first();
        if($doc->sync_status == 201){
            $doc->delete();
            return response()->json([], 204);
        }else{
            return response()->json([], 405);
        }
    }
    
    public static function updateDocDraft($invoiceId, $request)
    {

        $doc_number = DocNumber::where('temp_doc_number', $invoiceId)->update([
            'option_after_sync' => $request->option_after_sync,
            'doc_user' => $request->doc_user
        ]);
        $data['InvoiceStatusDate'] = Carbon::now();
        $data['SourceBilling'] = "P";
        $data['HASH'] = Str::random(4);
        $data['Period'] = '11';
        $data['InvoiceDate'] = Carbon::now();
        $data['InvoiceType'] = $request["InvoiceType"];
        $data['InvoiceTypeSerie'] = $request["InvoiceTypeSerie"];
        $data['SystemEntryDate'] = Carbon::now();
        $docEntidade = DocEntidade::find($request["CustomerID"]);
        if ($docEntidade == null) {
        $customer = DocEntidade::where('temp-CustomerID', $request["CustomerID"])->first();
        $data['CustomerID'] = $customer["CustomerID"];
        }else{
            $data['CustomerID'] = $request["CustomerID"];
        }    
        $data['CompanyID'] = $request['empresa_nif'];
        $data['AddressDetail'] = 'Lobito';
        $data['City'] = 'Lobito';
        $data['sync_status'] = 201;
        $data['Country'] = 'Angola';
        if (isset($request->InvoiceType) && $request->InvoiceType == "PP") {
            $data['SourceDocuments'] = "WorkingDocuments";
        }else{
            $data['SourceDocuments'] = "SalesInvoices";
        }
        $data['TaxPayable'] = $request->TaxPayable;
        $data['NetTotal'] = $request->NetTotal;
        $data['GrossTotal'] = $request->GrossTotal;
        $lines = $request['lines'];
        $doc = Docs::find($invoiceId);
        $doc->update($data);
        $arrayLines = DocService::addFieldInArrayLines($lines, $doc->InvoiceNo);
        $doc->docLinhas()->delete();
        $doc->docLinhas()->createMany($arrayLines);
        $doc->load("docLinhas");
        return $doc;
    }

    public static function getCustomerCurrentAccountV2($request, $nif, $customerId){
            
        $docsBalance = DocEntidadesSaldos::query()
                            ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                            ->selectRaw('SUM(CASE when InvoiceType in ("FT","SI","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as inicial_debito, 
                                        SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as inicial_credito, 
                                        (SUM(CASE  when InvoiceType in ("FT","ND","SI","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) - SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END)) as inicial_saldo')
                            ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                            ->where('CompanyID', $nif)
                            ->where('docs.CustomerID', $customerId)
                            ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                                $q->where('docs.InvoiceDate','<', $request->start_date);
                            })
                            ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                                $q->where('docs.InvoiceDate','<', Carbon::today());
                            })
                            ->groupBy(["CompanyID","docs.CustomerID"])
                            ->orderByRaw("InvoiceDate, docs.CustomerID")
                            ->get();

                            
        $queryCurrentAccount = Docs::query()
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('SUM(CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as final_debito, 
                            SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as final_credito, 
                            (SUM(CASE  when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) - SUM(CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END)) as final_saldo')
                ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate', [Carbon::today()->firstOfYear(), Carbon::today()]);
                })
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('CompanyID', $nif)
                ->where('docs.CustomerID', $customerId)
                ->groupBy(["docs.CompanyID","docs.CustomerID"])
                ->orderByRaw("InvoiceDate, docs.CustomerID")
                ->get();
                $docsCurrentAccount = $docsBalance->toArray();
                $docsCurrentAccount['0']['data_inicial']= (!isset($request->start_date) || !isset($request->end_date)) ? Carbon::today()->firstOfYear() : $request->start_date;
                $docsCurrentAccount['0']['data_final'] = (!isset($request->start_date) || !isset($request->end_date)) ? Carbon::today() : $request->end_date;
                $docsCurrentAccount['0']['final_debito'] = empty($queryCurrentAccount->toArray()) ? 0 : $queryCurrentAccount['0']->final_debito;
                $docsCurrentAccount['0']['final_credito'] = empty($queryCurrentAccount->toArray()) ? 0 : $queryCurrentAccount['0']->final_credito;
                $docsCurrentAccount['0']['final_saldo'] = empty($queryCurrentAccount->toArray()) ? 0 : $queryCurrentAccount['0']->final_saldo;
                $docsCurrentAccount['0']["key"] = "conta_corrente";
                $docsCurrentAccount['0']["description"] = "conta_corrente";
                $docsCurrentAccount['0']["documents"] = [];

        return $docsCurrentAccount;
    }

    public static function getCustomerCurrentAccountDetailsV2($request, $nif, $customerId){
        $queryCurrentAccountDocsLines = Docs::query()
                ->with('customer')
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->selectRaw('InvoiceId, InvoiceNo, InvoiceDate, InvoiceType, InvoiceTypeSerie, 
                            DocDescription, DocRef, CompanyID, docs.CustomerID, InvoiceStatus,
                            CASE when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END as debito, 
                            CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END as credito, 
                            (CASE  when InvoiceType in ("FT","ND","FG","GF") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END - CASE when InvoiceType in ("NC","RC") AND docs.InvoiceStatus <> "A" THEN GrossTotal ELSE 0 END) as saldo')
                ->whereIn('InvoiceType',["FT","ND","FG","GF","NC","RC"])
                ->when(!isset($request->start_date) || !isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate', [Carbon::today()->firstOfYear(), Carbon::today()]);
                // $q->where('docs.InvoiceDate', '<', Carbon::today());
                })
                ->when(isset($request->start_date) && isset($request->end_date), function($q) use($request){
                    $q->whereBetween('docs.InvoiceDate',[$request->start_date,$request->end_date]);
                })
                ->where('docs.CompanyID', $nif)
                ->where('docs.CustomerID', $customerId)
                ->orderByRaw("InvoiceDate, InvoiceNo")
                ->paginate(50);
                return $queryCurrentAccountDocsLines;

    }

    public static function getLastDocStatusCode($invoiceId) {
        $id = str_replace('@', '/', $invoiceId);
        $doc_statuses = DocStatus::where("InvoiceID", $id)
        ->orderBy("created_at", "DESC")
        ->groupBy("created_at")
        ->first();
        return $doc_statuses;
    }

    public static function getDocsDraftsOrderDetailsWithLinesByID($invoiceId){
        //$users_expositores_ids = UsersOnlineStoreService::getOnlineStoresByUserNif($nif);
        $id = str_replace('@', '/', $invoiceId);
        $docs = Docs::query()
                ->with(['docLinhas','customer','company', 'docStatuses'])
                ->join('doc_entidades','docs.CustomerID', '=', 'doc_entidades.CustomerID')
                ->join('doc_empresas','docs.CompanyID', '=', 'doc_empresas.CompanyID')
                ->join('online_stores', 'docs.CompanyID', '=', 'online_stores.CompanyID')
                ->select('docs.*','online_stores.StoreName as expositor_name','doc_entidades.CompanyName as customer_name')
                ->where('docs.InvoiceId', $id)
                //->where('docs.OnlineStorePO', 1)
                //->where("doc_status","<>", "concluído")
                ->orderBy("created_at", 'DESC')
                ->groupBy(["InvoiceTypeSerie", "InvoiceDate", "InvoiceNo"])
                ->first();

                //Adicionar o campo "doc_status_code" em cada documento
                if ($docs) {
                    $docs["doc_status_code"] = ($docs->docStatuses->last()) ? $docs->docStatuses->last()->status_id : 0;
                }
        return $docs;
    }

}
