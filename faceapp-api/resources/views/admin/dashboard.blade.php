@extends('admin.layout', ['title' => 'Overview'])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h2>Overview</h2>
            <p>Real-time summary of your devices, users, enrollments, and recent access events.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.devices.index') }}" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                Manage Devices
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Add User
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-card-label">Managed Devices</span>
                <div class="stat-card-icon" style="background:var(--primary-soft);color:var(--primary)">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                </div>
            </div>
            <div class="stat-card-value">{{ $managedDeviceCount }}</div>
            <div class="stat-card-sub">Total registered</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-card-label">Active Devices</span>
                <div class="stat-card-icon" style="background:var(--info-soft);color:var(--info)">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                </div>
            </div>
            <div class="stat-card-value">{{ $activeDeviceCount }}</div>
            <div class="stat-card-sub">Enabled for sync</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-card-label">Online Now</span>
                <div class="stat-card-icon" style="background:var(--good-soft);color:var(--good)">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                </div>
            </div>
            <div class="stat-card-value">{{ $onlineDeviceCount }}</div>
            <div class="stat-card-sub">Heartbeat recent</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-card-label">Managed Users</span>
                <div class="stat-card-icon" style="background:#fdf4ff;color:#9333ea">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zm8 0a3 3 0 11-6 0 3 3 0 016 0zm-4.07 11c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                </div>
            </div>
            <div class="stat-card-value">{{ $managedUserCount }}</div>
            <div class="stat-card-sub">In directory</div>
        </div>
    </div>

    <!-- Devices & Enrollments -->
    <div class="grid-2 mb-20">
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Devices</div>
                        <div class="card-subtitle">{{ $devices->count() }} managed</div>
                    </div>
                </div>
                <a href="{{ route('admin.devices.index') }}" class="btn btn-secondary btn-xs">View all</a>
            </div>
            @if ($devices->isEmpty())
                <div class="card-body">
                    <div class="empty-state" style="padding: 28px 20px">
                        <div class="empty-icon">📡</div>
                        <h4>No devices yet</h4>
                        <p>Add your first managed device to get started.</p>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Status</th>
                                <th>People</th>
                                <th>Faces</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td>
                                        <div class="td-primary">{{ $device->display_name }}</div>
                                        <div class="td-sub">{{ $device->device_key }}</div>
                                    </td>
                                    <td>
                                        @if ($device->is_online)
                                            <span class="badge badge-good">Online</span>
                                        @elseif ($device->last_seen_at)
                                            <span class="badge badge-warn">Stale</span>
                                        @else
                                            <span class="badge badge-bad">No heartbeat</span>
                                        @endif
                                    </td>
                                    <td>{{ $device->person_count ?? '—' }}</td>
                                    <td>{{ $device->face_count ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Recent Enrollments</div>
                        <div class="card-subtitle">Latest face enrollments</div>
                    </div>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-xs">View users</a>
            </div>
            @if ($recentEnrollments->isEmpty())
                <div class="card-body">
                    <div class="empty-state" style="padding: 28px 20px">
                        <div class="empty-icon">📷</div>
                        <h4>No enrollments yet</h4>
                        <p>Enrollments will appear here after users complete face capture.</p>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEnrollments as $enrollment)
                                <tr>
                                    <td>
                                        <div class="td-primary">{{ $enrollment->name }}</div>
                                        <div class="td-sub">{{ $enrollment->employee_id }}</div>
                                    </td>
                                    <td>
                                        @php $s = $enrollment->status; @endphp
                                        <span class="badge {{ $s === 'verified' ? 'badge-good' : ($s === 'partial' ? 'badge-warn' : 'badge-bad') }}">
                                            {{ ucfirst($s) }}
                                        </span>
                                    </td>
                                    <td class="text-muted text-sm">{{ $enrollment->updated_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Events -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/></svg>
                <div>
                    <div class="card-title">Recent Device Events</div>
                    <div class="card-subtitle">Heartbeats and access records</div>
                </div>
            </div>
            <a href="{{ route('devices.monitor.index') }}" class="btn btn-secondary btn-xs">Monitor</a>
        </div>
        @if ($recentEvents->isEmpty())
            <div class="card-body">
                <div class="empty-state" style="padding: 28px 20px">
                    <div class="empty-icon">📊</div>
                    <h4>No events captured</h4>
                    <p>Device heartbeats and access records will appear here once devices start reporting.</p>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Device</th>
                            <th>Person SN</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentEvents as $event)
                            <tr>
                                <td>
                                    <span class="badge badge-neutral">{{ str_replace('_', ' ', $event->event_type) }}</span>
                                </td>
                                <td class="font-semibold">{{ $event->device_key }}</td>
                                <td class="text-muted">{{ $event->person_sn ?: '—' }}</td>
                                <td class="text-muted text-sm">{{ $event->event_time?->diffForHumans() ?? $event->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
