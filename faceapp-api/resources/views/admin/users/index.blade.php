@extends('admin.layout', ['title' => 'Users'])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h2>Users</h2>
            <p>Manage the central user directory. Changes sync automatically to all active devices.</p>
        </div>
        <div class="page-header-actions">
            @if ($selectedDevice)
                <form method="post" action="{{ route('admin.devices.import-users', $selectedDevice) }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M3 10a1 1 0 011-1h7V6a1 1 0 112 0v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H4a1 1 0 01-1-1z"/></svg>
                        Pull From Device
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Add User
            </a>
        </div>
    </div>

    <!-- Device Filter Tabs -->
    <div class="tabs-bar">
        <a href="{{ route('admin.users.index', array_filter(['q' => $search])) }}"
           class="tab-item {{ !$selectedDeviceId ? 'active' : '' }}">
            All Devices
        </a>
        @foreach ($devices as $device)
            <a href="{{ route('admin.users.index', array_filter(['device_id' => $device->id, 'q' => $search])) }}"
               class="tab-item {{ $selectedDeviceId === $device->id ? 'active' : '' }}">
                {{ $device->display_name }}
                @if ($device->is_online)
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--good);margin-left:4px;vertical-align:middle;"></span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Search & Filter -->
    <div class="card mb-20">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="get" action="{{ route('admin.users.index') }}">
                @if ($selectedDeviceId)
                    <input type="hidden" name="device_id" value="{{ $selectedDeviceId }}">
                @endif
                <div class="search-bar">
                    <div class="search-field">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                        <input type="text" name="q" value="{{ $search }}"
                               placeholder="Search by name, employee ID, department, role…">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm" style="flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/></svg>
                        Filter
                    </button>
                    @if ($search)
                        <a href="{{ route('admin.users.index', $selectedDeviceId ? ['device_id' => $selectedDeviceId] : []) }}"
                           class="btn btn-ghost btn-sm" style="flex-shrink:0;">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zm8 0a3 3 0 11-6 0 3 3 0 016 0zm-4.07 11c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                <div>
                    <div class="card-title">
                        @if ($selectedDevice)
                            {{ $selectedDevice->display_name }} — Users
                        @else
                            User Directory
                        @endif
                    </div>
                    <div class="card-subtitle">
                        {{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }}
                        @if ($search) matching "{{ $search }}"@endif
                    </div>
                </div>
            </div>
            @if ($selectedDevice)
                <div class="text-sm text-muted">Pull users from the device to create or refresh local records and sync rows.</div>
            @endif
        </div>

        @if ($users->isEmpty())
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-icon">👤</div>
                    <h4>No users found</h4>
                    <p>
                        @if ($search)
                            No users match "{{ $search }}". Try a different search.
                        @else
                            Add your first user to get started with face access control.
                        @endif
                    </p>
                    @if (!$search)
                        <div style="margin-top: 18px;">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add User</a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Profile</th>
                            <th>Status</th>
                            <th>Sync Status</th>
                            <th>Latest Enrollment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @php
                                $latestEnrollment = $latestEnrollments->get($user->id)?->first();
                                $selectedSync = $selectedDeviceId ? $user->syncs->firstWhere('device_id', $selectedDeviceId) : null;
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="width:36px; height:36px; border-radius:50%; background:var(--primary-soft); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:var(--primary); flex-shrink:0; overflow:hidden;">
                                            @if ($user->photo_public_url)
                                                <img src="{{ $user->photo_public_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="td-primary truncate">{{ $user->name }}</div>
                                            <div class="td-sub">{{ $user->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 500;">{{ $user->role ?: '—' }}</div>
                                    <div class="td-sub">{{ $user->department ?: 'No department' }}</div>
                                    @if ($user->access_level)
                                        <div class="td-sub">Access: {{ $user->access_level }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'badge-good' : 'badge-neutral' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td style="max-width: 220px;">
                                    @if ($selectedDevice)
                                        @if ($selectedSync)
                                            @php
                                                $sf = $selectedSync->face_status;
                                                $ss = $selectedSync->sync_status;
                                                $cls = $sf === 'verified' ? 'badge-good' : ($ss === 'synced' ? 'badge-info' : ($ss === 'failed' ? 'badge-bad' : 'badge-warn'));
                                            @endphp
                                            <span class="badge {{ $cls }}">{{ $sf ?: $ss }}</span>
                                            @if ($selectedSync->last_error_message)
                                                <div class="td-sub" style="margin-top:4px;">{{ Str::limit($selectedSync->last_error_message, 60) }}</div>
                                            @endif
                                        @else
                                            <span class="badge badge-warn">Not synced</span>
                                            <div class="td-sub" style="margin-top:4px;">Not yet pushed to this device.</div>
                                        @endif
                                    @else
                                        @if ($user->syncs->isEmpty())
                                            <span class="badge badge-neutral">No syncs</span>
                                        @else
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                @foreach ($user->syncs->sortBy(fn ($s) => $s->device?->display_order ?? PHP_INT_MAX) as $sync)
                                                    @php
                                                        $sf = $sync->face_status;
                                                        $ss = $sync->sync_status;
                                                        $cls = $sf === 'verified' ? 'badge-good' : ($ss === 'synced' ? 'badge-info' : ($ss === 'failed' ? 'badge-bad' : 'badge-warn'));
                                                    @endphp
                                                    <span class="badge {{ $cls }}" title="{{ $sync->device?->display_name }}">
                                                        {{ $sync->device?->display_name ?? 'Unknown' }}: {{ $sf ?: $ss }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($latestEnrollment)
                                        @php $es = $latestEnrollment->status; @endphp
                                        <span class="badge {{ $es === 'verified' ? 'badge-good' : ($es === 'partial' ? 'badge-warn' : 'badge-bad') }}">
                                            {{ ucfirst($es) }}
                                        </span>
                                        <div class="td-sub" style="margin-top:4px;">{{ $latestEnrollment->updated_at?->diffForHumans() }}</div>
                                    @else
                                        <span class="text-muted text-xs">No enrollment yet</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="td-actions">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-xs">
                                            <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                            Edit
                                        </a>
                                        <form method="post" action="{{ route('admin.users.resync', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs" title="Resync to all devices">
                                                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                                                Resync
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Delete {{ addslashes($user->name) }} from records and all active devices?')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--bad);" title="Delete user">
                                                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zm-1 7a1 1 0 012 0v4a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="pagination-wrap">
                    <span class="pagination-info">
                        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
                    </span>
                    <div class="pagination">
                        {{-- Previous --}}
                        @if ($users->onFirstPage())
                            <span class="disabled"><span>
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span></span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </a>
                        @endif

                        {{-- Page numbers --}}
                        @php
                            $currentPage = $users->currentPage();
                            $lastPage = $users->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp
                        @if ($start > 1)
                            <a href="{{ $users->url(1) }}">1</a>
                            @if ($start > 2) <span class="disabled"><span>…</span></span> @endif
                        @endif
                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page === $currentPage)
                                <span class="active"><span>{{ $page }}</span></span>
                            @else
                                <a href="{{ $users->url($page) }}">{{ $page }}</a>
                            @endif
                        @endfor
                        @if ($end < $lastPage)
                            @if ($end < $lastPage - 1) <span class="disabled"><span>…</span></span> @endif
                            <a href="{{ $users->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        {{-- Next --}}
                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            </a>
                        @else
                            <span class="disabled"><span>
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            </span></span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
