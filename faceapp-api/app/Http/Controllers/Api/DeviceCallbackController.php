<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeviceCallbackController extends Controller
{
    public function heartbeat(Request $request, DeviceMonitoringService $monitoring): Response
    {
        $monitoring->recordHeartbeat($request->all());

        return response('ok', 200)->header('Content-Type', 'text/plain');
    }

    public function record(Request $request, DeviceMonitoringService $monitoring): Response
    {
        $monitoring->recordAccessRecord($request->all());

        return response('ok', 200)->header('Content-Type', 'text/plain');
    }

    public function personRegistration(Request $request, DeviceMonitoringService $monitoring): Response
    {
        $monitoring->recordPersonRegistration($request->all());

        return response('ok', 200)->header('Content-Type', 'text/plain');
    }
}
