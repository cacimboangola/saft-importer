<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorLogResource;
use App\Http\Services\ErrorLogService;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    private $errorLogService;

    public function __construct(ErrorLogService $errorLogService)
    {
        $this->errorLogService = $errorLogService;
    }

    public function index()
    {
        $errorLogs = $this->errorLogService->getErrorLogs();
        return ErrorLogResource::collection($errorLogs);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $errorLog = $this->errorLogService->createErrorLog($data);

        return new ErrorLogResource($errorLog);
    }

    public function destroy($id)
    {
        $this->errorLogService->deleteErrorLog($id);
        return response()->noContent();
    }
}