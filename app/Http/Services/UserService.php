<?php

namespace App\Http\Services;

use App\Models\User;
use Carbon\Carbon;

class UserService
{

    public static function changeLastCompanyIDUsed($user, $companyId){
        $user->setLastCompanyUsed($companyId);
        return $user->lastCompanyIDUsed;
    }

    public static function getEmpresas($user)
    {
       return $user->getEmpresas();
    }
}