<?php

use App\Http\Controllers\Api\AppDashboardController;
use App\Http\Controllers\Api\DeviceCallbackController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ExternalGateController;
use Illuminate\Support\Facades\Route;

Route::get('/app/dashboard', [AppDashboardController::class, 'show']);
Route::get('/device/status', [DeviceController::class, 'status']);
Route::post('/device/callbacks/heartbeat', [DeviceCallbackController::class, 'heartbeat'])
    ->name('api.devices.callbacks.heartbeat');
Route::post('/device/callbacks/records', [DeviceCallbackController::class, 'record'])
    ->name('api.devices.callbacks.record');
Route::post('/device/callbacks/person-registrations', [DeviceCallbackController::class, 'personRegistration'])
    ->name('api.devices.callbacks.person-registration');

Route::post('/enrollments', [EnrollmentController::class, 'store']);
Route::get('/enrollments/{enrollment:public_id}', [EnrollmentController::class, 'show']);

// External integration surface — bearer-token authed in the controller.
// Currently used by qparking-local to raise the turnstile after a paid exit.
Route::get('/external/health', [ExternalGateController::class, 'health']);
Route::post('/external/open-gate', [ExternalGateController::class, 'open']);
