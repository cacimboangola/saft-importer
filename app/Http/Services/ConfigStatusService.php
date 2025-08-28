<?php

namespace App\Http\Services;

use App\Models\ConfigStatus;

class ConfigStatusService
{
    public function getAllConfigStatus()
    {
        return ConfigStatus::all();
    }

    public function getConfigStatusById($id)
    {
        return ConfigStatus::findOrFail($id);
    }

    public function createConfigStatus($data)
    {
        return ConfigStatus::create($data);
    }

    public function updateConfigStatus($id, $data)
    {
        $configStatus = ConfigStatus::findOrFail($id);
        $configStatus->update($data);
        return $configStatus;
    }

    public function deleteConfigStatus($id)
    {
        $configStatus = ConfigStatus::findOrFail($id);
        $configStatus->delete();
    }
}
