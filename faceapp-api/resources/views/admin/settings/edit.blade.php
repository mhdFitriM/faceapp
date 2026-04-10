@extends('admin.layout', ['title' => 'System Settings'])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h2>System Settings</h2>
            <p>Global gateway URLs and retry configuration. Device credentials are stored per-device.</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('put')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">

            <!-- Gateway URLs -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        <span class="card-title">Gateway URLs</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-grid cols-1">
                        <div class="field">
                            <label for="gateway_base_url">Gateway Base URL</label>
                            <input id="gateway_base_url" name="gateway_base_url" type="url"
                                   value="{{ old('gateway_base_url', $settings->gateway_base_url) }}"
                                   placeholder="http://gateway:8190/api">
                            <span class="field-hint">Primary gateway API endpoint for device communication.</span>
                        </div>
                        <div class="field">
                            <label for="public_storage_base_url">Public Storage Base URL</label>
                            <input id="public_storage_base_url" name="public_storage_base_url" type="url"
                                   value="{{ old('public_storage_base_url', $settings->public_storage_base_url) }}"
                                   placeholder="https://face-api.example.com/storage">
                            <span class="field-hint">Public URL where stored photos can be accessed.</span>
                        </div>
                        <div class="field">
                            <label for="gateway_image_base_url">Gateway Image Base URL</label>
                            <input id="gateway_image_base_url" name="gateway_image_base_url" type="url"
                                   value="{{ old('gateway_image_base_url', $settings->gateway_image_base_url) }}"
                                   placeholder="http://face-api.example.com/storage">
                            <span class="field-hint">Base URL the gateway uses to fetch face images.</span>
                        </div>
                        <div class="field">
                            <label for="gateway_callback_base_url">Gateway Callback Base URL</label>
                            <input id="gateway_callback_base_url" name="gateway_callback_base_url" type="url"
                                   value="{{ old('gateway_callback_base_url', $settings->gateway_callback_base_url) }}"
                                   placeholder="http://face-api.example.com">
                            <span class="field-hint">Base URL registered on devices for callback events.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timing & Retry Settings -->
            <div style="display: grid; gap: 20px;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                            <span class="card-title">Heartbeat Timing</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="heartbeat_interval_seconds">Heartbeat Interval (s)</label>
                                <input id="heartbeat_interval_seconds" name="heartbeat_interval_seconds"
                                       type="number" min="10" max="300"
                                       value="{{ old('heartbeat_interval_seconds', $settings->heartbeat_interval_seconds ?: 60) }}">
                                <span class="field-hint">How often devices send a heartbeat. (10–300 s)</span>
                            </div>
                            <div class="field">
                                <label for="online_window_seconds">Online Window (s)</label>
                                <input id="online_window_seconds" name="online_window_seconds"
                                       type="number" min="30" max="900"
                                       value="{{ old('online_window_seconds', $settings->online_window_seconds ?: 180) }}">
                                <span class="field-hint">Max silence before device is marked offline. (30–900 s)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                            <span class="card-title">Person Verification Retries</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="person_verify_retries">Person Retries</label>
                                <input id="person_verify_retries" name="person_verify_retries"
                                       type="number" min="1" max="20"
                                       value="{{ old('person_verify_retries', $settings->person_verify_retries ?: 5) }}">
                                <span class="field-hint">Attempts to verify person creation on device.</span>
                            </div>
                            <div class="field">
                                <label for="person_verify_delay_ms">Person Retry Delay (ms)</label>
                                <input id="person_verify_delay_ms" name="person_verify_delay_ms"
                                       type="number" min="0" max="10000"
                                       value="{{ old('person_verify_delay_ms', $settings->person_verify_delay_ms ?: 1000) }}">
                                <span class="field-hint">Wait time between person verification retries.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            <span class="card-title">Face Verification Retries</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="face_verify_retries">Face Retries</label>
                                <input id="face_verify_retries" name="face_verify_retries"
                                       type="number" min="1" max="20"
                                       value="{{ old('face_verify_retries', $settings->face_verify_retries ?: 5) }}">
                                <span class="field-hint">Attempts to verify face upload on device.</span>
                            </div>
                            <div class="field">
                                <label for="face_verify_delay_ms">Face Retry Delay (ms)</label>
                                <input id="face_verify_delay_ms" name="face_verify_delay_ms"
                                       type="number" min="0" max="10000"
                                       value="{{ old('face_verify_delay_ms', $settings->face_verify_delay_ms ?: 1500) }}">
                                <span class="field-hint">Wait time between face verification retries.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div style="margin-top: 24px; display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Save Settings
            </button>
        </div>
    </form>

    <style>
        @media (max-width: 768px) {
            form > div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endsection
