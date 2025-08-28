<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigStatusResource;
use App\Http\Services\ConfigStatusService;

class ConfigStatusController extends Controller
{
    private $configStatusService;

    public function __construct(ConfigStatusService $configStatusService)
    {
        $this->configStatusService = $configStatusService;
    }

    public function index()
    {
        $configStatuses = $this->configStatusService->getAllConfigStatus();
        return ConfigStatusResource::collection($configStatuses);
    }

    public function show($id)
    {
        $configStatus = $this->configStatusService->getConfigStatusById($id);
        return new ConfigStatusResource($configStatus);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $configStatus = $this->configStatusService->createConfigStatus($data);

        return new ConfigStatusResource($configStatus);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        $configStatus = $this->configStatusService->updateConfigStatus($id, $data);

        return new ConfigStatusResource($configStatus);
    }

    public function destroy($id)
    {
        $this->configStatusService->deleteConfigStatus($id);

        return response()->json(204);
    }
}
