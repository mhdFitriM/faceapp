<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SystemSetting::singleton(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gateway_base_url' => ['nullable', 'url'],
            'public_storage_base_url' => ['nullable', 'url'],
            'gateway_image_base_url' => ['nullable', 'url'],
            'gateway_callback_base_url' => ['nullable', 'url'],
            'heartbeat_interval_seconds' => ['required', 'integer', 'between:10,300'],
            'online_window_seconds' => ['required', 'integer', 'between:30,900'],
            'person_verify_retries' => ['required', 'integer', 'between:1,20'],
            'person_verify_delay_ms' => ['required', 'integer', 'between:0,10000'],
            'face_verify_retries' => ['required', 'integer', 'between:1,20'],
            'face_verify_delay_ms' => ['required', 'integer', 'between:0,10000'],
        ]);

        SystemSetting::singleton()->update($validated);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'System settings updated.');
    }
}
