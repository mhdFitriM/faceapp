<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\Enrollment;
use App\Models\ManagedUser;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'managedDeviceCount' => Device::query()->where('is_managed', true)->count(),
            'activeDeviceCount' => Device::query()->where('is_managed', true)->where('is_active', true)->count(),
            'onlineDeviceCount' => Device::query()->get()->where('is_online', true)->count(),
            'managedUserCount' => ManagedUser::query()->count(),
            'recentEnrollments' => Enrollment::query()->latest()->limit(8)->get(),
            'recentEvents' => DeviceEvent::query()->latest('event_time')->latest()->limit(8)->get(),
            'devices' => Device::query()
                ->where('is_managed', true)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
