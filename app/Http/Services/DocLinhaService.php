<?php

namespace App\Http\Services;

use App\Http\Resources\DocLinhaResource;
use App\Models\DocLinha;
use Illuminate\Support\Str;

class DocLinhaService
{
    public static function insertDocLinha($request){
        $data['invoice_no'] = $request->invoice_no;
        $data['invoice_id'] = $request->invoice_id;
        $data['line_number'] = $request->line_number;
        $data['product_code'] = $request->product_code;
        $data['quantity'] = $request->quantity;
        $data['unit_of_measure'] = $request->unit_of_measure;
        $data['unit_price'] = $request->unit_price;
        $data['tax_point_date'] = $request->tax_point_date;
        $data['description'] = $request->description;
        $data['credit_amount'] = $request->credit_amount;
        $data['debit_amount'] = $request->debit_amount;
        $data['settlement_amount'] = $request->settlement_amount;
        $data['tax_type'] = $request->tax_type;
        $data['tax_country_region'] = $request->tax_country_region;
        $data['tax_code'] = $request->tax_code;
        $data['country'] = $request->country;
        $data['tax_payable'] = $request->tax_payable;
        $data['tax_percentage'] = $request->tax_percentage;
        $data['gross_total'] = $request->gross_total;
        $data['tax_exemption_reason'] = $request->tax_exemption_reason;
        $data['tax_exmption_code'] = $request->tax_exmption_code;
        $data['id_vendedor'] = $request->id_vendedor;
        $data['id_user_cacimbo_at'] = $request->id_user_cacimbo_at;
        $docLinha = DocLinha::create($data);
        return $docLinha;
    }
    public static function insertDocLinhaApi($request)
    {
        $data['ProductCode'] = $request["ProductCode"];
        $data['Quantity'] = $request["Quantity"];
        $data['UnitOfMeasure'] = $request["UnitOfMeasure"];
        $data['UnitPrice'] = $request["UnitPrice"];
        $data['TaxPointDate'] = $request["TaxPointDate"];
        $data['Description'] = $request["Description"];
        $data['CreditAmount'] = $request["Quantity"] * $request["UnitPrice"]; 
        $data['DebitAmount'] = $request["Quantity"] * $request["UnitPrice"]; 
        $data['SettlementAmount'] = $request["SettlementAmount"];
        $data['TaxType'] = $request["TaxType"];
        $data['TaxCountryRegion'] = $request["TaxCountryRegion"];
        $data['TaxCode'] = $request["TaxCode"];
        $data['TaxPercentage'] = $request["TaxPercentage"];
        $data['InvoiceNo'] = $request["InvoiceNo"];
        $data['InvoiceId'] = $request['InvoiceId'];
        $data['idArmazem'] = $request['idArmazem'];
        $data['LineNumber'] =  $request["InvoiceNo"] . '-line-temp-'.random_int(1, 1000) ;
        $docLinha = DocLinha::create($data);
        return $docLinha;
    }
    
    public static function insertDocLinhaBySaft($requests, $invoiceData){
        if (isset($requests['Line']['0'])) {
           foreach ($requests['Line'] as $request) {
            $data['LineNumber'] =  $requests['empresa_nif'] . "-".$request["LineNumber"]."-" .$requests["InvoiceNo"] ;
            $data['ProductCode'] = $request["ProductCode"];
            $data['Quantity'] = $request["Quantity"];
            $data['UnitOfMeasure'] = $request["UnitOfMeasure"];
            $data['UnitPrice'] = $request["UnitPrice"];
            $data['TaxPointDate'] = $request["TaxPointDate"];
            $data['Description'] = $request["Description"];
            $data['CreditAmount'] = (isset($request["CreditAmount"])) ? $request["CreditAmount"] : 0 ; 
            $data['DebitAmount'] = (isset($request["DebitAmount"])) ? $request["DebitAmount"] : 0 ; 
            $data['SettlementAmount'] = $request["SettlementAmount"];
            $data['TaxType'] = $request['Tax']["TaxType"];
            $data['TaxCountryRegion'] = $request['Tax']["TaxCountryRegion"];
            $data['TaxCode'] = $request['Tax']["TaxCode"];
            //$data['tax_payable'] = $request['Line']['DocumentTotals']["TaxPayable"];
            $data['TaxPercentage'] = $request['Tax']["TaxPercentage"];
            //$data['gross_total'] = $request['DocumentTotals']["GrossTotal"];
            $data['InvoiceNo'] = $requests["InvoiceNo"];
            $data['InvoiceId'] = $requests['empresa_nif']."-" .$requests["InvoiceNo"];
            $docLinha = DocLinha::create($data);
           }
        }else{
            $data['LineNumber'] =  $requests['empresa_nif'] . "-".$requests['Line']["LineNumber"] ."-" .$requests["InvoiceNo"];
            $data['ProductCode'] = $requests['Line']["ProductCode"];
            $data['Quantity'] = $requests['Line']["Quantity"];
            $data['UnitOfMeasure'] = $requests['Line']["UnitOfMeasure"];
            $data['UnitPrice'] = $requests['Line']["UnitPrice"];
            $data['TaxPointDate'] = $requests['Line']["TaxPointDate"];
            $data['Description'] = $requests['Line']["Description"];
            $data['CreditAmount'] = (isset($requests['Line']["CreditAmount"])) ? $requests['Line']["CreditAmount"] : 0 ; 
            $data['DebitAmount'] = (isset($requests['Line']["DebitAmount"])) ? $requests['Line']["DebitAmount"] : 0 ; 
            $data['SettlementAmount'] = $requests['Line']["SettlementAmount"];
            $data['TaxType'] = $requests['Line']['Tax']["TaxType"];
            $data['TaxCountryRegion'] = $requests['Line']['Tax']["TaxCountryRegion"];
            $data['TaxCode'] = $requests['Line']['Tax']["TaxCode"];
            //$data['tax_payable'] = $request['Line']['DocumentTotals']["TaxPayable"];
            $data['TaxPercentage'] = $requests['Line']['Tax']["TaxPercentage"];
            //$data['gross_total'] = $request['DocumentTotals']["GrossTotal"];
            $data['InvoiceNo'] = $requests["InvoiceNo"];
            $data['InvoiceId'] = $requests['empresa_nif']."-" .$requests["InvoiceNo"];
            $docLinha = DocLinha::create($data);
        }
        
    }

    public static function getAllDocLinhas(){
        $docLinha = DocLinha::all();
        return $docLinha;
    }

}
