<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\GatewaySdkClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DeviceController extends Controller
{
    public function status(Request $request, GatewaySdkClient $gateway): JsonResponse
    {
        try {
            $device = Device::query()
                ->where('is_managed', true)
                ->find($request->integer('device_id'));

            $response = $device
                ? $gateway->forDevice($device)->deviceStatus()
                : $gateway->deviceStatus();
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Device status check failed.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Device status retrieved successfully.',
            'gateway_response' => $response,
        ]);
    }
}
