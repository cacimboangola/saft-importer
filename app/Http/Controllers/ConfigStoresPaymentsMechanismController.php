<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigStoresPaymentsMechanismResource;
use App\Http\Services\ConfigStoresPaymentsMechanismService;
use App\Models\ConfigStoresPaymentsMechanism;

class ConfigStoresPaymentsMechanismController extends Controller
{
    private $mechanismService;

    public function __construct(ConfigStoresPaymentsMechanismService $mechanismService)
    {
        $this->mechanismService = $mechanismService;
    }

    public function index() {
        
        $mechanism = $this->mechanismService->index();

        return ConfigStoresPaymentsMechanismResource::collection($mechanism);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Mechanism' => 'required',
            'Description' => 'required',
        ]);

        $mechanism = $this->mechanismService->create($data);

        return new ConfigStoresPaymentsMechanismResource($mechanism);
    }

    public function update(Request $request, ConfigStoresPaymentsMechanism $mechanism)
    {
        $data = $request->validate([
            'Mechanism' => 'required',
            'Description' => 'required',
        ]);

        $mechanism = $this->mechanismService->update($mechanism, $data);

        return new ConfigStoresPaymentsMechanismResource($mechanism);
    }

    public function destroy(ConfigStoresPaymentsMechanism $mechanism)
    {
        $this->mechanismService->delete($mechanism);

        return response()->noContent();
    }
}
