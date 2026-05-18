<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\GatewaySdkClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * External integration surface for non-FaceApp callers (currently:
 * qparking-local, which fires this after a successful exit payment to
 * raise the turnstile barrier).
 *
 * Authentication: a shared static token in the Authorization header,
 * configured via env FACEAPP_EXTERNAL_API_TOKEN. Single token covers all
 * external callers — rotate the env value to invalidate.
 *
 * Under the hood we proxy to the gateway SDK's /device/output endpoint
 * with type=1 (open the door — the same call the device's own access
 * record callbacks trigger when a face is matched).
 */
class ExternalGateController extends Controller
{
    /**
     * POST /api/external/open-gate
     * Body:
     *   device_id (int, optional) — managed Device row id. When omitted we
     *                                fall back to the default gateway device.
     *   reason    (string, optional) — for the audit log (e.g. "qparking-exit-paid")
     *   plate     (string, optional) — for the audit log (vehicle's plate)
     *
     * Returns:
     *   { ok: bool, device_id, reason, plate, gateway_response, error? }
     */
    public function open(Request $request, GatewaySdkClient $gateway): JsonResponse
    {
        $this->authorise($request);

        $deviceId = $request->integer('device_id') ?: null;
        $reason   = (string) ($request->input('reason') ?? 'external');
        $plate    = $request->input('plate');

        try {
            if ($deviceId) {
                $device = Device::query()->where('is_managed', true)->find($deviceId);
                if (!$device) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'unknown_device',
                        'message' => "No managed device with id={$deviceId}.",
                    ], 404);
                }
                $response = $gateway->forDevice($device)->output(type: 1);
            } else {
                $response = $gateway->output(type: 1);
            }

            // Audit log so we can correlate qparking-local "paid" events
            // with the actual turnstile activation later.
            \Log::info('external open-gate', [
                'caller_ip' => $request->ip(),
                'device_id' => $deviceId,
                'reason'    => $reason,
                'plate'     => $plate,
            ]);

            return response()->json([
                'ok'               => true,
                'device_id'        => $deviceId,
                'reason'           => $reason,
                'plate'            => $plate,
                'gateway_response' => $response,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'gateway_failed',
                'message' => $e->getMessage(),
                'device_id' => $deviceId,
                'reason' => $reason,
                'plate' => $plate,
            ], 502);
        }
    }

    /** Lightweight health probe so qparking-local can verify creds + URL. */
    public function health(Request $request): JsonResponse
    {
        $this->authorise($request);
        return response()->json([
            'ok' => true,
            'service' => 'faceapp-api/external',
            'now' => now()->toIso8601String(),
        ]);
    }

    protected function authorise(Request $request): void
    {
        $expected = config('services.external_api.token') ?? env('FACEAPP_EXTERNAL_API_TOKEN');
        if (!$expected) {
            abort(503, 'external API disabled — set FACEAPP_EXTERNAL_API_TOKEN');
        }
        $supplied = trim((string) $request->bearerToken());
        if (!hash_equals((string) $expected, $supplied)) {
            abort(401, 'invalid token');
        }
    }
}
