{{-- Shared device form fields. Pass $device (null for create, model for edit) --}}
<div class="form-section-label">Identity</div>

<div class="field">
    <label for="device_key">Device Key <span class="req">*</span></label>
    <input id="device_key" name="device_key"
           value="{{ old('device_key', $device?->device_key) }}"
           placeholder="e.g. GATE01"
           {{ $device ? 'readonly style=opacity:.6;background:var(--surface-2)' : 'required' }}>
    @if ($device)
        <span class="field-hint">Device key cannot be changed after creation.</span>
    @endif
</div>

<div class="field">
    <label for="device_name">Display Name</label>
    <input id="device_name" name="name"
           value="{{ old('name', $device?->name) }}"
           placeholder="Main Lobby Gate">
</div>

<div class="field">
    <label for="client_name">Client</label>
    <input id="client_name" name="client_name"
           value="{{ old('client_name', $device?->client_name) }}"
           placeholder="Client or company name">
</div>

<div class="field">
    <label for="branch_name">Branch / Store</label>
    <input id="branch_name" name="branch_name"
           value="{{ old('branch_name', $device?->branch_name) }}"
           placeholder="Branch or location name">
</div>

<div class="form-section-label">Credentials</div>

<div class="field">
    <label for="device_secret">Device Secret {{ $device ? '' : '<span class="req">*</span>' }}</label>
    <input id="device_secret" name="secret" type="password"
           placeholder="{{ $device ? 'Leave blank to keep current secret' : 'Required' }}"
           {{ $device ? '' : 'required' }}>
</div>

<div class="field">
    <label for="display_order">Display Order</label>
    <input id="display_order" name="display_order" type="number" min="0"
           value="{{ old('display_order', $device?->display_order ?? 0) }}">
    <span class="field-hint">Lower number appears first in lists.</span>
</div>

<div class="form-section-label">Defaults</div>

<div class="field">
    <label for="person_type_default">Default Person Type</label>
    <select id="person_type_default" name="person_type_default">
        @foreach ([1 => 'Employee', 2 => 'Guest', 3 => 'Blacklist'] as $value => $label)
            <option value="{{ $value }}" @selected(old('person_type_default', $device?->person_type_default ?? 1) == $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label for="verify_style_default">Default Verify Style</label>
    <input id="verify_style_default" name="verify_style_default" type="number" min="0"
           value="{{ old('verify_style_default', $device?->verify_style_default ?? 1) }}">
</div>

<div class="field">
    <label for="ac_group_number_default">Default Access Group</label>
    <input id="ac_group_number_default" name="ac_group_number_default" type="number" min="0"
           value="{{ old('ac_group_number_default', $device?->ac_group_number_default ?? 0) }}">
</div>

<div class="field">
    <label for="photo_quality_default">Photo Quality Check</label>
    <select id="photo_quality_default" name="photo_quality_default">
        <option value="1" @selected(old('photo_quality_default', $device?->photo_quality_default ?? 1) == 1)>Loose</option>
        <option value="0" @selected(old('photo_quality_default', $device?->photo_quality_default ?? 1) == 0)>Strict</option>
    </select>
</div>

<div class="field col-span-2">
    <div class="checkbox-field">
        <input id="is_active" name="is_active" type="checkbox" value="1"
               @checked(old('is_active', $device?->is_active ?? true))>
        <label for="is_active">Active — enable for user sync and face enrollment</label>
    </div>
</div>

<div class="field col-span-2">
    <label for="device_notes">Notes</label>
    <textarea id="device_notes" name="notes"
              placeholder="Optional deployment notes, location details, etc.">{{ old('notes', $device?->notes) }}</textarea>
</div>
