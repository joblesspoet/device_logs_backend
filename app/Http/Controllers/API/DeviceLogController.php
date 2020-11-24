<?php

namespace App\Http\Controllers\API;

use App\Models\DeviceLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceLogsResource;

class DeviceLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = DeviceLog::query();
        return DeviceLogsResource::collection(
            $query->orderBy('id', 'desc')->paginate(100)
        );
    }

}
