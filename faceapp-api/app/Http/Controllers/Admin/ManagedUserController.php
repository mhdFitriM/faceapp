<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Enrollment;
use App\Models\ManagedUser;
use App\Services\ManagedUserSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ManagedUserController extends Controller
{
    public function index(Request $request): View
    {
        $devices = Device::query()
            ->where('is_managed', true)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $selectedDeviceId = $request->integer('device_id') ?: null;
        $selectedDevice = $devices->firstWhere('id', $selectedDeviceId);
        $search = trim((string) $request->query('q', ''));

        $users = ManagedUser::query()
            ->with(['syncs.device'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('employee_id', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('role', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $latestEnrollments = Enrollment::query()
            ->whereIn('managed_user_id', $users->getCollection()->pluck('id'))
            ->latest()
            ->get()
            ->groupBy('managed_user_id');

        return view('admin.users.index', [
            'devices' => $devices,
            'selectedDeviceId' => $selectedDeviceId,
            'selectedDevice' => $selectedDevice,
            'search' => $search,
            'users' => $users,
            'latestEnrollments' => $latestEnrollments,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'managedUser' => new ManagedUser([
                'person_type' => 1,
                'verify_style' => 1,
                'ac_group_number' => 0,
                'is_active' => true,
            ]),
            'isEditing' => false,
        ]);
    }

    public function edit(ManagedUser $managedUser): View
    {
        return view('admin.users.form', [
            'managedUser' => $managedUser,
            'isEditing' => true,
        ]);
    }

    public function store(Request $request, ManagedUserSyncService $syncs): RedirectResponse
    {
        $managedUser = ManagedUser::query()->create($this->validateUser($request));
        $syncSummary = $this->syncUser($syncs, $managedUser);

        return redirect()
            ->route('admin.users.edit', $managedUser)
            ->with('status', 'User created. '.$syncSummary);
    }

    public function update(Request $request, ManagedUser $managedUser, ManagedUserSyncService $syncs): RedirectResponse
    {
        $managedUser->update($this->validateUser($request, $managedUser));
        $syncSummary = $this->syncUser($syncs, $managedUser);

        return redirect()
            ->route('admin.users.edit', $managedUser)
            ->with('status', 'User updated. '.$syncSummary);
    }

    public function destroy(ManagedUser $managedUser, ManagedUserSyncService $syncs): RedirectResponse
    {
        $results = $syncs->deleteUserAcrossActiveDevices($managedUser);
        $managedUser->delete();

        $failures = collect($results)->where('status', 'failed')->count();

        return redirect()
            ->route('admin.users.index')
            ->with('status', $failures === 0 ? 'User deleted from local records and active devices.' : 'User deleted locally, but '.$failures.' device deletions failed.');
    }

    public function resync(ManagedUser $managedUser, ManagedUserSyncService $syncs): RedirectResponse
    {
        $summary = $this->syncUser($syncs, $managedUser);

        return back()->with('status', 'Resync finished. '.$summary);
    }

    protected function validateUser(Request $request, ?ManagedUser $managedUser = null): array
    {
        return $request->validate([
            'employee_id' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('managed_users', 'employee_id')->ignore($managedUser?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'access_level' => ['nullable', 'string', 'max:255'],
            'joined_on' => ['nullable', 'date'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'card_no' => ['nullable', 'string', 'max:255'],
            'id_card' => ['nullable', 'string', 'max:255'],
            'voucher_code' => ['nullable', 'string', 'max:255'],
            'verify_pwd' => ['nullable', 'string', 'max:255'],
            'person_type' => ['required', 'integer', 'in:1,2,3'],
            'verify_style' => ['required', 'integer', 'min:0'],
            'ac_group_number' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function syncUser(ManagedUserSyncService $syncs, ManagedUser $managedUser): string
    {
        try {
            $results = $syncs->syncUserAcrossActiveDevices($managedUser);
        } catch (Throwable $exception) {
            return 'Saved locally, but device sync was skipped: '.$exception->getMessage();
        }

        $failed = collect($results)->where('status', 'failed')->count();

        return $failed === 0
            ? 'Synced to all active devices.'
            : 'Synced with '.$failed.' device failures.';
    }
}
