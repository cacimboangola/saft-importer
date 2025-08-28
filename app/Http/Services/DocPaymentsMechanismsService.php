<?php

namespace App\Http\Services;

use App\Models\DocPaymentsMechanism;
use App\Models\UserEmpresa;

class DocPaymentsMechanismsService
{
    public static function getAllPaymetsMechanismsByCompany($nif)
    {
       return DocPaymentsMechanism::query()
                            ->where("CompanyID", $nif)
                            ->get();
    }
}