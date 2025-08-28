<?php
namespace App\Http\Services;

use App\Models\ConfigStoresPaymentsMechanism;

class ConfigStoresPaymentsMechanismService
{
    public function index(){
        return ConfigStoresPaymentsMechanism::all();
    }
    public function create($data)
    {
        return ConfigStoresPaymentsMechanism::create($data);
    }

    public function update(ConfigStoresPaymentsMechanism $mechanism, $data)
    {
        $mechanism->update($data);
        return $mechanism;
    }

    public function delete(ConfigStoresPaymentsMechanism $mechanism)
    {
        $mechanism->delete();
    }
}
