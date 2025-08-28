<?php

namespace App\Http\Services;

use App\Models\DocBankAccount;
use App\Models\UserEmpresa;

class DocBankAccountService
{

    public static function getAllDocBankAccountsByCompany($nif)
    {
       return DocBankAccount::query()
                            ->where("CompanyID", $nif)
                            ->get();
    }
   
}