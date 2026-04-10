@extends('admin.layout', ['title' => 'Callback Monitor'])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h2>Callback Monitor</h2>
            <p>Live view of device connectivity, callback URLs, recent access events, and enrollment status. Auto-refreshes every 30 seconds.</p>
        </div>
        <div class="page-header-actions">
            <span class="topbar-badge" style="background:var(--good-soft);color:var(--good-text);font-size:12px;padding:5px 12px;border-radius:999px;font-weight:600;">
                <span id="refresh-dot" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--good);margin-right:5px;vertical-align:middle;animation:pulse 2s infinite;"></span>
                Live — refreshes every 30s
            </span>
        </div>
    </div>

    <style>
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
        .kv-table { width: 100%; border-collapse: collapse; }
        .kv-table tr:last-child td { border-bottom: none; }
        .kv-table td { padding: 10px 0; border-bottom: 1px solid var(--border-soft); vertical-align: top; font-size: 14px; }
        .kv-table td:first-child { font-weight: 600; color: var(--text-2); width: 180px; padding-right: 16px; white-space: nowrap; }
        .kv-table td:last-child { color: var(--muted); word-break: break-all; }
        .code-tag { font-family: monospace; font-size: 12px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 5px; padding: 2px 6px; color: var(--text-2); }
        @media (max-width: 600px) { .kv-table td:first-child { width: 120px; font-size: 12px; } }
    </style>

    <meta http-equiv="refresh" content="30">

    <!-- Device selector tabs -->
    @if ($managedDevices->isNotEmpty())
        <div class="tabs-bar mb-20">
            @foreach ($managedDevices as $device)
                <a href="{{ route('devices.monitor.index', ['device_id' => $device->id]) }}"
                   class="tab-item {{ $selectedDevice?->is($device) ? 'active' : '' }}">
                    @if ($device->is_online)
                        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--good);margin-right:5px;vertical-align:middle;"></span>
                    @else
                        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--muted-light);margin-right:5px;vertical-align:middle;"></span>
                    @endif
                    {{ $device->display_name }}
                </a>
            @endforeach
        </div>
    @endif

    <!-- Top row: Gateway check + Callback URLs -->
    <div class="grid-2 mb-20">

        <!-- Gateway Live Check -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Gateway Live Check</div>
                        <div class="card-subtitle">
                            @if ($selectedDevice)
                                {{ $selectedDevice->display_name }}
                            @else
                                {{ $configuredDeviceKey ?: 'No device configured' }}
                            @endif
                        </div>
                    </div>
                </div>
                @if ($gatewayError)
                    <span class="badge badge-bad">Error</span>
                @elseif ($gatewayStatus)
                    <span class="badge badge-good">Reachable</span>
                @else
                    <span class="badge badge-neutral">No response</span>
                @endif
            </div>
            <div class="card-body">
                <table class="kv-table">
                    <tr>
                        <td>Selected device</td>
                        <td>
                            @if ($selectedDevice)
                                <span style="font-weight:600;color:var(--text);">{{ $selectedDevice->display_name }}</span>
                                <div><span class="code-tag">{{ $selectedDevice->device_key }}</span></div>
                            @else
                                <span class="code-tag">{{ $configuredDeviceKey ?: 'Not set' }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Heartbeat interval</td>
                        <td>{{ $heartbeatIntervalSeconds }}s</td>
                    </tr>
                    <tr>
                        <td>Online window</td>
                        <td>{{ $onlineWindowSeconds }}s</td>
                    </tr>
                    <tr>
                        <td>Gateway result</td>
                        <td>
                            @if ($gatewayError)
                                <span class="badge badge-bad">Gateway Error</span>
                                <div style="margin-top:6px;font-size:12px;color:var(--bad-text);">{{ $gatewayError }}</div>
                            @elseif ($gatewayStatus)
                                <span class="badge badge-good">Gateway Reachable</span>
                            @else
                                <span class="badge badge-neutral">No Response Yet</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 16px;">
                    <form method="post" action="{{ route('devices.monitor.configure') }}">
                        @csrf
                        @if ($selectedDevice)
                            <input type="hidden" name="device_id" value="{{ $selectedDevice->id }}">
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            Push Callback URLs to Device
                        </button>
                    </form>
                </div>

                @if ($gatewayStatus)
                    <div style="margin-top: 16px;">
                        <div class="text-xs text-muted" style="margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Raw gateway response</div>
                        <pre style="font-size:12px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);padding:12px;overflow:auto;max-height:200px;line-height:1.5;">{{ json_encode($gatewayStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif

                @if (session('gateway_config_response'))
                    <div style="margin-top: 16px;">
                        <div class="text-xs text-muted" style="margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Latest callback config response</div>
                        <pre style="font-size:12px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);padding:12px;overflow:auto;max-height:200px;line-height:1.5;">{{ json_encode(session('gateway_config_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>
        </div>

        <!-- Callback URLs -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Callback URLs</div>
                        <div class="card-subtitle">Registered on device via gateway config</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="kv-table">
                    <tr>
                        <td>Access records</td>
                        <td><span class="code-tag">{{ $callbackUrls['record'] }}</span></td>
                    </tr>
                    <tr>
                        <td>Heartbeat</td>
                        <td><span class="code-tag">{{ $callbackUrls['heartbeat'] }}</span></td>
                    </tr>
                    <tr>
                        <td>Person reg.</td>
                        <td><span class="code-tag">{{ $callbackUrls['person_registration'] }}</span></td>
                    </tr>
                </table>
                <p class="text-sm text-muted" style="margin-top:16px;line-height:1.6;">
                    If using the cloud middleware flow, these URLs must be configured on the device through the middleware server configuration. Use the <strong>Push Callback URLs</strong> button to set them via <code style="font-size:11px;">device/setSevConfig</code>.
                </p>
            </div>
        </div>
    </div>

    <!-- All Devices Table -->
    <div class="card mb-20">
        <div class="card-header">
            <div class="card-header-left">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                <div>
                    <div class="card-title">All Devices</div>
                    <div class="card-subtitle">Ordered by last activity</div>
                </div>
            </div>
        </div>
        @if ($devices->isEmpty())
            <div class="card-body">
                <div class="empty-state" style="padding:28px 20px">
                    <div class="empty-icon">📡</div>
                    <h4>No device callbacks received yet</h4>
                    <p>Once your device is configured and sends a heartbeat, it will appear here.</p>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Device Key</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>IP</th>
                            <th>Version</th>
                            <th>People</th>
                            <th>Faces</th>
                            <th>Last Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $device)
                            <tr>
                                <td>
                                    <span class="code-tag">{{ $device->device_key }}</span>
                                    @if ($device->name)
                                        <div class="td-sub">{{ $device->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($device->is_online)
                                        <span class="badge badge-good">Online</span>
                                    @else
                                        <span class="badge badge-warn">No recent heartbeat</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($device->last_seen_at)
                                        <div style="font-size:13px;font-weight:500;">{{ $device->last_seen_at->toDateTimeString() }}</div>
                                        <div class="td-sub">{{ $device->last_seen_at->diffForHumans() }}</div>
                                    @else
                                        <span class="text-muted text-xs">Never</span>
                                    @endif
                                </td>
                                <td class="text-muted text-sm">{{ $device->last_ip ?: '—' }}</td>
                                <td class="text-muted text-sm">{{ $device->last_version ?: '—' }}</td>
                                <td class="text-muted">{{ $device->person_count ?? '—' }}</td>
                                <td class="text-muted">{{ $device->face_count ?? '—' }}</td>
                                <td>
                                    @if ($device->last_record_at)
                                        <div style="font-size:13px;">{{ $device->last_record_at->toDateTimeString() }}</div>
                                        <div class="td-sub">{{ $device->last_record_at->diffForHumans() }}</div>
                                    @else
                                        <span class="text-muted text-xs">No record yet</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent Events + Enrollments -->
    <div class="grid-2">

        <!-- Recent Device Events -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Recent Device Events</div>
                        <div class="card-subtitle">Latest 20 heartbeats &amp; access records</div>
                    </div>
                </div>
            </div>
            @if ($recentEvents->isEmpty())
                <div class="card-body">
                    <div class="empty-state" style="padding:24px 20px">
                        <div class="empty-icon">📊</div>
                        <h4>No events yet</h4>
                        <p>Heartbeat and access record callbacks will appear here.</p>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Device</th>
                                <th>Person</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEvents as $event)
                                <tr>
                                    <td>
                                        <div style="font-size:12px;font-weight:500;">
                                            {{ ($event->event_time ?? $event->created_at)->toDateTimeString() }}
                                        </div>
                                        <div class="td-sub">{{ ($event->event_time ?? $event->created_at)->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral" style="font-size:11px;">
                                            {{ str_replace('_', ' ', $event->event_type) }}
                                        </span>
                                    </td>
                                    <td><span class="code-tag">{{ $event->device_key }}</span></td>
                                    <td class="text-muted text-sm">{{ $event->person_sn ?: '—' }}</td>
                                    <td class="text-muted text-sm">{{ $event->result_flag ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent Enrollments -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Recent Enrollments</div>
                        <div class="card-subtitle">Latest 20 face enrollments</div>
                    </div>
                </div>
            </div>
            @if ($recentEnrollments->isEmpty())
                <div class="card-body">
                    <div class="empty-state" style="padding:24px 20px">
                        <div class="empty-icon">📷</div>
                        <h4>No enrollments yet</h4>
                        <p>Face enrollment results will appear here once users complete the process.</p>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Status</th>
                                <th>Device</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEnrollments as $enrollment)
                                @php $es = $enrollment->status; @endphp
                                <tr>
                                    <td>
                                        <div class="td-primary">{{ $enrollment->name }}</div>
                                        <div class="td-sub">{{ $enrollment->employee_id }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $es === 'verified' ? 'badge-good' : ($es === 'failed' ? 'badge-bad' : 'badge-warn') }}">
                                            {{ $es }}
                                        </span>
                                        @if ($enrollment->error_message)
                                            <div style="font-size:11px;color:var(--bad-text);margin-top:4px;">{{ Str::limit($enrollment->error_message, 50) }}</div>
                                        @endif
                                        @if ($enrollment->gateway_face_response || $enrollment->verification_response)
                                            <details style="margin-top:6px;">
                                                <summary style="font-size:11px;cursor:pointer;color:var(--muted);font-weight:600;">Raw response</summary>
                                                <pre style="font-size:11px;margin-top:6px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px;overflow:auto;max-height:140px;line-height:1.4;">{{ json_encode([
                                                    'person' => $enrollment->gateway_person_response,
                                                    'face'   => $enrollment->gateway_face_response,
                                                    'verify' => $enrollment->verification_response,
                                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($enrollment->device_key)
                                            <span class="code-tag">{{ $enrollment->device_key }}</span>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size:12px;">{{ optional($enrollment->updated_at)->toDateTimeString() }}</div>
                                        <div class="td-sub">{{ optional($enrollment->updated_at)->diffForHumans() }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
