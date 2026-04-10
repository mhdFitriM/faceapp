@extends('admin.layout', ['title' => 'Devices'])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h2>Devices</h2>
            <p>Manage face terminals, configure callbacks, and trigger remote actions per device.</p>
        </div>
        <div class="page-header-actions">
            @if ($editingDevice)
                <a href="{{ route('admin.devices.index') }}" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Add New Device
                </a>
            @else
                <button class="btn btn-primary btn-sm" onclick="openModal('deviceModal')">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Add Device
                </button>
            @endif
        </div>
    </div>

    <!-- Devices Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                <div>
                    <div class="card-title">Managed Devices</div>
                    <div class="card-subtitle">{{ $devices->count() }} device{{ $devices->count() !== 1 ? 's' : '' }} registered</div>
                </div>
            </div>
        </div>

        @if ($devices->isEmpty())
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-icon">📡</div>
                    <h4>No devices yet</h4>
                    <p>Add your first face terminal to start managing access control.</p>
                    <div style="margin-top: 18px;">
                        <button class="btn btn-primary btn-sm" onclick="openModal('deviceModal')">Add Device</button>
                    </div>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Client / Branch</th>
                            <th>State</th>
                            <th>Online</th>
                            <th>People</th>
                            <th>Faces</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $device)
                            <tr>
                                <td>
                                    <div class="td-primary">{{ $device->display_name }}</div>
                                    <div class="td-sub" style="font-family: monospace; font-size: 11px;">{{ $device->device_key }}</div>
                                </td>
                                <td>
                                    @if ($device->client_name || $device->branch_name)
                                        <div class="font-semibold" style="font-size: 13px;">{{ $device->client_name ?? '—' }}</div>
                                        <div class="td-sub">{{ $device->branch_name ?? '—' }}</div>
                                    @else
                                        <span class="text-muted text-sm">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($device->is_managed)
                                        <span class="badge {{ $device->is_active ? 'badge-good' : 'badge-warn' }}">
                                            {{ $device->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">Callback only</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($device->is_online)
                                        <span class="badge badge-good">Online</span>
                                    @elseif ($device->last_seen_at)
                                        <span class="badge badge-warn">Stale</span>
                                    @else
                                        <span class="badge badge-bad">No heartbeat</span>
                                    @endif
                                    <div class="td-sub" style="margin-top: 4px;">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</div>
                                </td>
                                <td class="text-muted">{{ $device->person_count ?? '—' }}</td>
                                <td class="text-muted">{{ $device->face_count ?? '—' }}</td>
                                <td>
                                    <div class="td-actions">
                                        <a href="{{ route('admin.devices.edit', $device) }}"
                                           class="btn btn-secondary btn-xs"
                                           title="Edit device">
                                            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                            Edit
                                        </a>
                                        <button class="btn btn-ghost btn-xs" onclick="toggleMenu(this, {{ $device->id }})" title="More actions">
                                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                        </button>

                                        {{-- Hidden menu template for this device --}}
                                        <template class="device-menu-tpl" data-id="{{ $device->id }}">
                                            <form method="post" action="{{ route('admin.devices.status', $device) }}">
                                                @csrf
                                                <button type="submit" class="menu-item">
                                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                                    Refresh Status
                                                </button>
                                            </form>
                                                <form method="post" action="{{ route('admin.devices.configure-callbacks', $device) }}">
                                                    @csrf
                                                    <button type="submit" class="menu-item">
                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                        Setup Callbacks
                                                    </button>
                                                </form>
                                                <form method="post" action="{{ route('admin.devices.import-users', $device) }}">
                                                    @csrf
                                                    <button type="submit" class="menu-item">
                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h3a1 1 0 010 2h-3v5a1 1 0 11-2 0v-5H6a1 1 0 010-2h3V4a1 1 0 011-1z"/><path d="M4 6a2 2 0 012-2h1a1 1 0 110 2H6v10h8V6h-1a1 1 0 010-2h1a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                                                        Pull Users
                                                    </button>
                                                </form>
                                                <form method="post" action="{{ route('admin.devices.open-door', $device) }}">
                                                    @csrf
                                                    <button type="submit" class="menu-item">
                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                                        Open Door
                                                </button>
                                            </form>
                                            <form method="post" action="{{ route('admin.devices.reboot', $device) }}">
                                                @csrf
                                                <button type="submit" class="menu-item">
                                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                                                    Reboot Device
                                                </button>
                                            </form>
                                            <form method="post" action="{{ route('admin.devices.resync-users', $device) }}">
                                                @csrf
                                                <button type="submit" class="menu-item">
                                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zm0 2a6 6 0 016 6H2a6 6 0 016-6zm6-8a2 2 0 100 4 2 2 0 000-4zm0 6a5.98 5.98 0 012.5.55 4.002 4.002 0 01-2.494 3.432A5.995 5.995 0 0014 9z"/></svg>
                                                    Resync Users
                                                </button>
                                            </form>
                                            <hr class="menu-divider">
                                            <form method="post" action="{{ route('admin.devices.destroy', $device) }}" onsubmit="return confirm('Remove this device from managed devices?')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="menu-item menu-item-danger">
                                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zm-1 7a1 1 0 012 0v4a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"/></svg>
                                                    Delete Device
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Edit Device inline (when $editingDevice is set) -->
    @if ($editingDevice)
        <div class="card mt-20">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                    <div>
                        <div class="card-title">Edit Device</div>
                        <div class="card-subtitle">{{ $editingDevice->display_name }} &mdash; {{ $editingDevice->device_key }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.devices.update', $editingDevice) }}" class="form-grid">
                    @csrf
                    @method('put')
                    @include('admin.devices._form', ['device' => $editingDevice])
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('admin.devices.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Add Device Modal -->
    @if (!$editingDevice)
        <div class="modal-backdrop" id="deviceModal">
            <div class="modal">
                <div class="modal-header">
                    <span class="modal-title">Add New Device</span>
                    <button class="modal-close" onclick="closeModal('deviceModal')" type="button" aria-label="Close">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
                <form method="post" action="{{ route('admin.devices.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-grid">
                            @include('admin.devices._form', ['device' => null])
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('deviceModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Device</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($errors->any() || old('device_key'))
            <script>document.addEventListener('DOMContentLoaded', function() { openModal('deviceModal'); });</script>
        @endif
    @endif

    <!-- Floating action menu (appended to body to escape overflow clipping) -->
    <div id="floatingMenu" style="display:none; position:fixed; z-index:9999; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); min-width:178px; overflow:hidden;" onclick="event.stopPropagation()">
        <div id="floatingMenuContent"></div>
    </div>

    <style>
        .menu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 9px 14px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-2);
            text-align: left;
            transition: background 0.1s;
            font-family: inherit;
        }
        .menu-item:hover { background: var(--bg); }
        .menu-item-danger { color: var(--bad); }
        .menu-item-danger:hover { background: var(--bad-soft); }
        .menu-divider { border: none; border-top: 1px solid var(--border-soft); margin: 4px 0; }
    </style>

    <script>
        // Store the menus' HTML keyed by device id from <template> elements
        const deviceMenus = {};
        document.querySelectorAll('template.device-menu-tpl').forEach(tpl => {
            deviceMenus[tpl.dataset.id] = tpl.innerHTML;
        });

        const floatingMenu = document.getElementById('floatingMenu');
        const floatingContent = document.getElementById('floatingMenuContent');
        let activeBtn = null;

        function toggleMenu(btn, deviceId) {
            if (activeBtn === btn && floatingMenu.style.display === 'block') {
                closeFloatingMenu();
                return;
            }
            closeFloatingMenu();
            activeBtn = btn;
            floatingContent.innerHTML = deviceMenus[deviceId] || '';
            floatingMenu.style.display = 'block';
            positionMenu(btn);
            setTimeout(() => {
                document.addEventListener('click', outsideClick);
            }, 0);
        }

        function positionMenu(btn) {
            const rect = btn.getBoundingClientRect();
            const menuH = floatingMenu.offsetHeight || 240;
            const menuW = floatingMenu.offsetWidth || 178;
            const spaceBelow = window.innerHeight - rect.bottom;
            let top = spaceBelow > menuH + 8 ? rect.bottom + 4 : rect.top - menuH - 4;
            let left = rect.right - menuW;
            if (left < 8) left = 8;
            floatingMenu.style.top = top + 'px';
            floatingMenu.style.left = left + 'px';
        }

        function closeFloatingMenu() {
            floatingMenu.style.display = 'none';
            activeBtn = null;
            document.removeEventListener('click', outsideClick);
        }

        function outsideClick(e) {
            if (!floatingMenu.contains(e.target)) closeFloatingMenu();
        }

        // Reposition on scroll/resize
        window.addEventListener('scroll', () => { if (activeBtn) positionMenu(activeBtn); }, true);
        window.addEventListener('resize', () => { if (activeBtn) positionMenu(activeBtn); });
    </script>
@endsection
