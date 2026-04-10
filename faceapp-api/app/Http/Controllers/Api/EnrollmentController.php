<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Models\Enrollment;
use App\Services\FaceEnrollmentService;
use Illuminate\Http\JsonResponse;
use Throwable;

class EnrollmentController extends Controller
{
    public function store(StoreEnrollmentRequest $request, FaceEnrollmentService $service): JsonResponse
    {
        try {
            $enrollment = $service->enroll($request->validated());
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Face enrollment failed.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'message' => match ($enrollment->status) {
                'partial' => 'Face enrollment completed, but some devices still need attention.',
                'verified' => 'Face enrollment verified on active devices.',
                default => 'Face enrollment completed.',
            },
            'enrollment' => $this->transform($enrollment),
        ], 201);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'enrollment' => $this->transform($enrollment),
        ]);
    }

    protected function transform(Enrollment $enrollment): array
    {
        return [
            'public_id' => $enrollment->public_id,
            'managed_user_id' => $enrollment->managed_user_id,
            'employee_id' => $enrollment->employee_id,
            'name' => $enrollment->name,
            'status' => $enrollment->status,
            'device_key' => $enrollment->device_key,
            'photo_public_url' => $enrollment->photo_public_url,
            'gateway_person_status' => $enrollment->gateway_person_status,
            'gateway_face_status' => $enrollment->gateway_face_status,
            'sync_results' => $enrollment->sync_results,
            'error_message' => $enrollment->error_message,
            'enrolled_at' => $enrollment->enrolled_at?->toIso8601String(),
            'created_at' => $enrollment->created_at?->toIso8601String(),
            'updated_at' => $enrollment->updated_at?->toIso8601String(),
        ];
    }
}
