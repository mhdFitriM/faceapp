@extends('admin.layout', ['title' => $isEditing ? 'Edit User' : 'Add User'])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h2>{{ $isEditing ? 'Edit User' : 'Add User' }}</h2>
            <p>User records sync automatically to all active face terminals when saved.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to Users
            </a>
            @if ($isEditing)
                <form method="post" action="{{ route('admin.users.resync', $managedUser) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                        Resync Now
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid-2" style="align-items: start;">
        <!-- Main form -->
        <div style="grid-column: 1 / -1;">
            <form method="post" action="{{ $isEditing ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">
                @csrf
                @if ($isEditing) @method('put') @endif

                <!-- Identity Card -->
                <div class="card mb-20">
                    <div class="card-header">
                        <div class="card-header-left">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            <span class="card-title">Identity</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="employee_id">Employee ID <span class="req">*</span></label>
                                <input id="employee_id" name="employee_id"
                                       value="{{ old('employee_id', $managedUser->employee_id) }}"
                                       placeholder="e.g. EMP001"
                                       required>
                                <span class="field-hint">Alphanumeric only. Used as device person key.</span>
                            </div>
                            <div class="field">
                                <label for="name">Full Name <span class="req">*</span></label>
                                <input id="name" name="name"
                                       value="{{ old('name', $managedUser->name) }}"
                                       placeholder="e.g. John Doe"
                                       required>
                            </div>
                            <div class="field">
                                <label for="role">Role / Job Title</label>
                                <input id="role" name="role"
                                       value="{{ old('role', $managedUser->role) }}"
                                       placeholder="e.g. Software Engineer">
                            </div>
                            <div class="field">
                                <label for="department">Department</label>
                                <input id="department" name="department"
                                       value="{{ old('department', $managedUser->department) }}"
                                       placeholder="e.g. Engineering">
                            </div>
                            <div class="field">
                                <label for="access_level">Access Level</label>
                                <input id="access_level" name="access_level"
                                       value="{{ old('access_level', $managedUser->access_level) }}"
                                       placeholder="e.g. L2 or Admin">
                            </div>
                            <div class="field">
                                <label for="joined_on">Joined On</label>
                                <input id="joined_on" name="joined_on" type="date"
                                       value="{{ old('joined_on', optional($managedUser->joined_on)->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact & Credentials Card -->
                <div class="card mb-20">
                    <div class="card-header">
                        <div class="card-header-left">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                            <span class="card-title">Contact & Credentials</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="mobile">Mobile</label>
                                <input id="mobile" name="mobile"
                                       value="{{ old('mobile', $managedUser->mobile) }}"
                                       placeholder="+60 12 345 6789">
                            </div>
                            <div class="field">
                                <label for="card_no">Card Number</label>
                                <input id="card_no" name="card_no"
                                       value="{{ old('card_no', $managedUser->card_no) }}"
                                       placeholder="Access card / RFID number">
                            </div>
                            <div class="field">
                                <label for="id_card">ID Card</label>
                                <input id="id_card" name="id_card"
                                       value="{{ old('id_card', $managedUser->id_card) }}"
                                       placeholder="National ID or passport">
                            </div>
                            <div class="field">
                                <label for="voucher_code">Voucher Code</label>
                                <input id="voucher_code" name="voucher_code"
                                       value="{{ old('voucher_code', $managedUser->voucher_code) }}">
                            </div>
                            <div class="field">
                                <label for="verify_pwd">Verify Password</label>
                                <input id="verify_pwd" name="verify_pwd" type="password"
                                       value="{{ old('verify_pwd', $managedUser->verify_pwd) }}"
                                       placeholder="Optional PIN/password for device">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device Settings Card -->
                <div class="card mb-20">
                    <div class="card-header">
                        <div class="card-header-left">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                            <span class="card-title">Device Settings</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="person_type">Person Type</label>
                                <select id="person_type" name="person_type">
                                    @foreach ([1 => 'Employee', 2 => 'Guest', 3 => 'Blacklist'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('person_type', $managedUser->person_type ?? 1) == $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="verify_style">Verify Style</label>
                                <input id="verify_style" name="verify_style" type="number" min="0"
                                       value="{{ old('verify_style', $managedUser->verify_style ?? 1) }}">
                                <span class="field-hint">Device-specific verification mode integer.</span>
                            </div>
                            <div class="field">
                                <label for="ac_group_number">Access Group</label>
                                <input id="ac_group_number" name="ac_group_number" type="number" min="0"
                                       value="{{ old('ac_group_number', $managedUser->ac_group_number ?? 0) }}">
                            </div>
                            <div class="field" style="display:flex; align-items:center;">
                                <div class="checkbox-field" style="padding-top: 22px;">
                                    <input id="is_active" name="is_active" type="checkbox" value="1"
                                           @checked(old('is_active', $managedUser->is_active ?? true))>
                                    <label for="is_active">Active — eligible for device sync</label>
                                </div>
                            </div>
                            <div class="field col-span-2">
                                <label for="notes">Notes</label>
                                <textarea id="notes" name="notes"
                                          placeholder="Optional internal notes about this user…">{{ old('notes', $managedUser->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($isEditing && $managedUser->photo_public_url)
                    <!-- Face Photo Card -->
                    <div class="card mb-20">
                        <div class="card-header">
                            <div class="card-header-left">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                                <span class="card-title">Face Photo</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-sm mb-16">Latest photo captured through the face enrollment app.</p>
                            <img src="{{ $managedUser->photo_public_url }}" alt="Face photo"
                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: var(--radius-lg); border: 1px solid var(--border);">
                        </div>
                    </div>
                @endif

                <!-- Submit -->
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $isEditing ? 'Save Changes' : 'Create User' }}
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    @if ($isEditing)
                        <form method="post" action="{{ route('admin.users.destroy', $managedUser) }}"
                              onsubmit="return confirm('Permanently delete this user from records and all active devices?')"
                              style="margin-left: auto;">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm" style="align-self:center;">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zm-1 7a1 1 0 012 0v4a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"/></svg>
                                Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if ($isEditing)
        <!-- Sync Status Card -->
        <div class="card mt-20">
            <div class="card-header">
                <div class="card-header-left">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                    <div>
                        <div class="card-title">Device Sync Status</div>
                        <div class="card-subtitle">Per-device synchronization state for this user</div>
                    </div>
                </div>
            </div>
            @if ($managedUser->syncs->isEmpty())
                <div class="card-body">
                    <p class="text-muted text-sm">This user has not been synced to any device yet. Save to trigger a sync.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Sync Status</th>
                                <th>Face Status</th>
                                <th>Last Synced</th>
                                <th>Last Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($managedUser->syncs->load('device') as $sync)
                                <tr>
                                    <td class="td-primary">{{ $sync->device?->display_name ?? 'Unknown' }}</td>
                                    <td>
                                        @php $ss = $sync->sync_status; @endphp
                                        <span class="badge {{ $ss === 'synced' ? 'badge-good' : ($ss === 'failed' ? 'badge-bad' : 'badge-warn') }}">
                                            {{ $ss ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $sf = $sync->face_status; @endphp
                                        @if ($sf)
                                            <span class="badge {{ $sf === 'verified' ? 'badge-good' : ($sf === 'failed' ? 'badge-bad' : 'badge-warn') }}">
                                                {{ $sf }}
                                            </span>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted text-sm">{{ $sync->last_synced_at?->diffForHumans() ?? '—' }}</td>
                                    <td>
                                        @if ($sync->last_error_message)
                                            <span class="text-xs" style="color:var(--bad)">{{ Str::limit($sync->last_error_message, 80) }}</span>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
@endsection
