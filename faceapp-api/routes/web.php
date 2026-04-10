<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManagedDeviceController;
use App\Http\Controllers\Admin\ManagedUserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\DeviceMonitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/devices', [ManagedDeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [ManagedDeviceController::class, 'store'])->name('devices.store');
    Route::get('/devices/{device}/edit', [ManagedDeviceController::class, 'edit'])->name('devices.edit');
    Route::put('/devices/{device}', [ManagedDeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{device}', [ManagedDeviceController::class, 'destroy'])->name('devices.destroy');
    Route::post('/devices/{device}/status', [ManagedDeviceController::class, 'status'])->name('devices.status');
    Route::post('/devices/{device}/configure-callbacks', [ManagedDeviceController::class, 'configureCallbacks'])->name('devices.configure-callbacks');
    Route::post('/devices/{device}/import-users', [ManagedDeviceController::class, 'importUsers'])->name('devices.import-users');
    Route::post('/devices/{device}/open-door', [ManagedDeviceController::class, 'openDoor'])->name('devices.open-door');
    Route::post('/devices/{device}/reboot', [ManagedDeviceController::class, 'reboot'])->name('devices.reboot');
    Route::post('/devices/{device}/resync-users', [ManagedDeviceController::class, 'resyncUsers'])->name('devices.resync-users');

    Route::get('/users', [ManagedUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [ManagedUserController::class, 'create'])->name('users.create');
    Route::post('/users', [ManagedUserController::class, 'store'])->name('users.store');
    Route::get('/users/{managedUser}/edit', [ManagedUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{managedUser}', [ManagedUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{managedUser}', [ManagedUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{managedUser}/resync', [ManagedUserController::class, 'resync'])->name('users.resync');
});

Route::get('/devices', [DeviceMonitorController::class, 'index'])->name('devices.monitor.index');
Route::post('/devices/configure-callbacks', [DeviceMonitorController::class, 'configureCallbacks'])
    ->name('devices.monitor.configure');
