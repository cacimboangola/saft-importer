<?php

namespace App\Http\Services;

use App\Models\DocType;
use App\Models\Docs;
use App\Models\UserEmpresa;
//use Carbon/Carbon;

class DocTypeService
{
    public static function getAllDocTypesByCompany($nif)
    {
       return DocType::query()
                            ->where("CompanyID", $nif)
                            ->whereIn("SourceDocuments",["SalesInvoices","WorkingDocuments"])
                            ->get();
    }

    /*public static function getAllDocTypesByCompany($nif)
    {
       return Docs::query()
                        ->selectRaw("docs.InvoiceTypeSerie, doc_types.*")
                        ->rightJoin("doc_types", "docs.InvoiceTypeSerie", "=", "doc_types.InvoiceTypeSerie")
                        ->where("docs.CompanyID", $nif)
                        ->whereIn("docs.SourceDocuments",["SalesInvoices","WorkingDocuments"])
                        ->groupByRaw("docs.CompanyID, docs.InvoiceTypeSerie,docs.InvoiceType")
                        ->distinct()
                        ->get();
    }*/


    public static function getAllDocTypes()
    {
       return DocType::query()
                     ->whereIn("SourceDocuments",["SalesInvoices","WorkingDocuments"])
                     ->get();
    }

    public static function getDocType($id)
    {
        return DocType::find($id);
    }
    
}