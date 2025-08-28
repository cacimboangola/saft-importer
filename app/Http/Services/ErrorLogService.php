<?php

namespace App\Http\Services;

use App\Models\ErrorLog;

class ErrorLogService
{
    public function createErrorLog(array $data)
    {
        return ErrorLog::create($data);
    }

    public function getErrorLogs()
    {
        return ErrorLog::all();
    }

    public function deleteErrorLog($id)
    {
        $errorLog = ErrorLog::find($id);
        if ($errorLog) {
            $errorLog->delete();
            return true;
        }
        return false;
    }
}