<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\ManagedUser;
use App\Services\GatewaySdkClient;
use App\Services\ManagedUserSyncService;
use App\Services\SystemSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ManagedDeviceController extends Controller
{
    public function index(): View
    {
        return $this->view();
    }

    public function edit(Device $device): View
    {
        return $this->view($device);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDevice($request);
        $validated['is_managed'] = true;

        Device::query()->create($validated);

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'Device added.');
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $this->validateDevice($request, $device);
        $validated['is_managed'] = true;

        if (($validated['secret'] ?? '') === '') {
            unset($validated['secret']);
        }

        $device->update($validated);

        return redirect()
            ->route('admin.devices.edit', $device)
            ->with('status', 'Device updated.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $name = $device->display_name;
        $device->delete();

        return redirect()
            ->route('admin.devices.index')
            ->with('status', $name.' was removed from managed devices.');
    }

    public function status(Device $device, GatewaySdkClient $gateway): RedirectResponse
    {
        try {
            $response = $gateway->forDevice($device)->deviceStatus();
            $this->applyStatusPayload($device, $response['data'] ?? []);
        } catch (Throwable $exception) {
            return back()->with('error', 'Status check failed: '.$exception->getMessage());
        }

        return back()
            ->with('status', 'Device status refreshed.')
            ->with('action_response', $response);
    }

    public function configureCallbacks(Device $device, GatewaySdkClient $gateway, SystemSettingsService $settings): RedirectResponse
    {
        try {
            $response = $gateway->forDevice($device)->setServerConfig([
                'sevUploadRecRecordUrl' => $this->callbackUrl($settings, '/api/device/callbacks/records'),
                'sevUploadDevHeartbeatUrl' => $this->callbackUrl($settings, '/api/device/callbacks/heartbeat'),
                'sevUploadRegPersonUrl' => $this->callbackUrl($settings, '/api/device/callbacks/person-registrations'),
                'sevUploadDevHeartbeatInterval' => $settings->heartbeatIntervalSeconds(),
            ]);
        } catch (Throwable $exception) {
            return back()->with('error', 'Callback configuration failed: '.$exception->getMessage());
        }

        return back()
            ->with('status', 'Callback URLs pushed to '.$device->display_name.'.')
            ->with('action_response', $response);
    }

    public function openDoor(Device $device, GatewaySdkClient $gateway): RedirectResponse
    {
        try {
            $response = $gateway->forDevice($device)->output(1);
        } catch (Throwable $exception) {
            return back()->with('error', 'Open door failed: '.$exception->getMessage());
        }

        return back()
            ->with('status', 'Open door command sent to '.$device->display_name.'.')
            ->with('action_response', $response);
    }

    public function reboot(Device $device, GatewaySdkClient $gateway): RedirectResponse
    {
        try {
            $response = $gateway->forDevice($device)->reboot();
        } catch (Throwable $exception) {
            return back()->with('error', 'Reboot failed: '.$exception->getMessage());
        }

        return back()
            ->with('status', 'Reboot command sent to '.$device->display_name.'.')
            ->with('action_response', $response);
    }

    public function resyncUsers(Device $device, ManagedUserSyncService $syncs): RedirectResponse
    {
        $results = ManagedUser::query()
            ->where('is_active', true)
            ->orderBy('employee_id')
            ->get()
            ->map(function (ManagedUser $user) use ($syncs, $device): array {
                try {
                    $syncs->syncUserToDevice($user, $device);

                    return ['status' => 'synced', 'employee_id' => $user->employee_id];
                } catch (Throwable $exception) {
                    return ['status' => 'failed', 'employee_id' => $user->employee_id, 'error' => $exception->getMessage()];
                }
            })
            ->all();

        $failed = collect($results)->where('status', 'failed')->count();

        return back()
            ->with('status', $failed === 0 ? 'All active users were synced to '.$device->display_name.'.' : 'User resync finished with '.$failed.' failures.')
            ->with('action_response', $results);
    }

    public function importUsers(Device $device, ManagedUserSyncService $syncs): RedirectResponse
    {
        try {
            $results = $syncs->importUsersFromDevice($device);
        } catch (Throwable $exception) {
            return back()->with('error', 'Import users failed: '.$exception->getMessage());
        }

        $imported = count($results);

        return redirect()
            ->route('admin.users.index', [
                'device_id' => $device->id,
            ])
            ->with('status', $imported === 0
                ? 'No users were returned by '.$device->display_name.'.'
                : 'Imported or refreshed '.$imported.' user'.($imported === 1 ? '' : 's').' from '.$device->display_name.'.')
            ->with('action_response', $results);
    }

    protected function view(?Device $editingDevice = null): View
    {
        return view('admin.devices.index', [
            'devices' => Device::query()
                ->orderByDesc('is_managed')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(),
            'editingDevice' => $editingDevice,
        ]);
    }

    protected function validateDevice(Request $request, ?Device $device = null): array
    {
        $secretRule = $device ? ['nullable', 'string', 'max:255'] : ['required', 'string', 'max:255'];

        return $request->validate([
            'device_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('devices', 'device_key')->ignore($device?->id),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'secret' => $secretRule,
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'person_type_default' => ['required', 'integer', 'in:1,2,3'],
            'verify_style_default' => ['required', 'integer', 'min:0'],
            'ac_group_number_default' => ['required', 'integer', 'min:0'],
            'photo_quality_default' => ['required', 'integer', 'in:0,1'],
            'notes' => ['nullable', 'string'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function callbackUrl(SystemSettingsService $settings, string $path): string
    {
        $baseUrl = $settings->gatewayCallbackBaseUrl();

        if ($baseUrl === '') {
            return url($path);
        }

        return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
    }

    protected function applyStatusPayload(Device $device, array $payload): void
    {
        $device->forceFill([
            'last_version' => $payload['versionCode'] ?? $device->last_version,
            'person_count' => $payload['personCount'] ?? $device->person_count,
            'face_count' => $payload['photoCount'] ?? $device->face_count,
        ])->save();
    }
}
