<?php
// app/Services/OnlinePaymentsMechanismService.php

namespace App\Http\Services;

use App\Models\OnlinePaymentsMechanism;

class OnlinePaymentsMechanismService
{
    public function createMechanism($data)
    {
        return OnlinePaymentsMechanism::create($data);
    }

    public function updateMechanism(OnlinePaymentsMechanism $mechanism, $data)
    {
        $mechanism->update($data);
        return $mechanism;
    }

    public function deleteMechanism(OnlinePaymentsMechanism $mechanism)
    {
        $mechanism->delete();
    }

    public function getMechanismById($id)
    {
        return OnlinePaymentsMechanism::findOrFail($id);
    }

    public function getAllMechanisms()
    {
        return OnlinePaymentsMechanism::all();
    }
}
