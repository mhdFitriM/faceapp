<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\Enrollment;
use App\Services\GatewaySdkClient;
use App\Services\SystemSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class DeviceMonitorController extends Controller
{
    public function index(Request $request, GatewaySdkClient $gateway, SystemSettingsService $settings): View
    {
        $managedDevices = Device::query()
            ->where('is_managed', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $selectedDevice = $managedDevices->firstWhere('id', $request->integer('device_id'))
            ?? $managedDevices->first();

        $gatewayStatus = null;
        $gatewayError = null;
        $callbackUrls = [
            'record' => $this->callbackUrl($settings, '/api/device/callbacks/records'),
            'heartbeat' => $this->callbackUrl($settings, '/api/device/callbacks/heartbeat'),
            'person_registration' => $this->callbackUrl($settings, '/api/device/callbacks/person-registrations'),
        ];

        try {
            if ($selectedDevice) {
                $gatewayStatus = $gateway->forDevice($selectedDevice)->deviceStatus();
            } elseif (filled(config('gateway.device_key'))) {
                $gatewayStatus = $gateway->deviceStatus();
            }
        } catch (Throwable $exception) {
            $gatewayError = $exception->getMessage();
        }

        return view('devices.index', [
            'managedDevices' => $managedDevices,
            'selectedDevice' => $selectedDevice,
            'configuredDeviceKey' => $selectedDevice?->device_key ?: config('gateway.device_key'),
            'gatewayStatus' => $gatewayStatus,
            'gatewayError' => $gatewayError,
            'devices' => Device::query()
                ->orderByDesc('last_seen_at')
                ->orderByDesc('updated_at')
                ->get(),
            'recentEvents' => DeviceEvent::query()
                ->latest('event_time')
                ->latest()
                ->limit(20)
                ->get(),
            'recentEnrollments' => Enrollment::query()
                ->latest()
                ->limit(20)
                ->get(),
            'callbackUrls' => $callbackUrls,
            'heartbeatIntervalSeconds' => $settings->heartbeatIntervalSeconds(),
            'onlineWindowSeconds' => $settings->onlineWindowSeconds(),
        ]);
    }

    public function configureCallbacks(Request $request, GatewaySdkClient $gateway, SystemSettingsService $settings): RedirectResponse
    {
        $selectedDevice = Device::query()
            ->where('is_managed', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->find($request->integer('device_id'))
            ?? Device::query()
                ->where('is_managed', true)
                ->orderBy('display_order')
                ->orderBy('name')
                ->first();

        $callbackUrls = [
            'record' => $this->callbackUrl($settings, '/api/device/callbacks/records'),
            'heartbeat' => $this->callbackUrl($settings, '/api/device/callbacks/heartbeat'),
            'person_registration' => $this->callbackUrl($settings, '/api/device/callbacks/person-registrations'),
        ];

        try {
            $client = $selectedDevice ? $gateway->forDevice($selectedDevice) : $gateway;

            if (! $selectedDevice && blank(config('gateway.device_key'))) {
                throw new \RuntimeException('No managed device is available for callback configuration yet.');
            }

            $response = $client->setServerConfig([
                'sevUploadRecRecordUrl' => $callbackUrls['record'],
                'sevUploadDevHeartbeatUrl' => $callbackUrls['heartbeat'],
                'sevUploadRegPersonUrl' => $callbackUrls['person_registration'],
                'sevUploadDevHeartbeatInterval' => $settings->heartbeatIntervalSeconds(),
            ]);
        } catch (Throwable $exception) {
            return redirect()
                ->route('devices.monitor.index', array_filter([
                    'device_id' => $selectedDevice?->id,
                ]))
                ->with('error', 'Failed to push callback URLs to the gateway: '.$exception->getMessage());
        }

        return redirect()
            ->route('devices.monitor.index', array_filter([
                'device_id' => $selectedDevice?->id,
            ]))
            ->with('status', 'Callback URLs pushed to '.($selectedDevice?->display_name ?: 'the device').' through the gateway.')
            ->with('gateway_config_response', $response);
    }

    protected function callbackUrl(SystemSettingsService $settings, string $path): string
    {
        $baseUrl = $settings->gatewayCallbackBaseUrl();

        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
        }

        return url($path);
    }
}
