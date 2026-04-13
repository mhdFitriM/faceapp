<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GatewaySdkClient
{
    protected ?Device $deviceContext = null;

    public function __construct(
        protected readonly SystemSettingsService $settings,
    ) {}

    public function forDevice(Device $device): self
    {
        $clone = clone $this;
        $clone->deviceContext = $device;

        return $clone;
    }

    public function deviceStatus(): array
    {
        return $this->post('/device/get');
    }

    public function reboot(): array
    {
        return $this->post('/device/reboot');
    }

    public function reset(int $type): array
    {
        return $this->post('/device/reset', [
            'type' => $type,
        ]);
    }

    public function output(int $type = 1, ?string $content = null): array
    {
        return $this->post('/device/output', [
            'type' => $type,
            'content' => $content,
        ]);
    }

    public function setServerConfig(array $config): array
    {
        return $this->post('/device/setSevConfig', $config);
    }

    public function setConfig(array $config): array
    {
        return $this->post('/device/setConfig', $config);
    }

    public function findPerson(string $employeeId): array
    {
        return $this->post('/person/find', [
            'type' => 1,
            'key' => $employeeId,
        ], requireBusinessSuccess: false);
    }

    public function findPersonList(int $index = 1, int $length = 20): array
    {
        return $this->post('/person/findList', [
            'index' => $index,
            'length' => $length,
        ]);
    }

    public function createPerson(array $person): array
    {
        return $this->post('/person/create', $this->personPayload($person));
    }

    public function updatePerson(array $person): array
    {
        return $this->post('/person/update', $this->personPayload($person));
    }

    public function mergePerson(array $person): array
    {
        return $this->post('/person/merge', $this->personPayload($person));
    }

    public function upsertPerson(array $person): array
    {
        return $this->mergePerson($person);
    }

    public function mergeFace(string $employeeId, string $imageUrl, string $imageBase64, int $photoQuality = 1): array
    {
        return $this->post('/face/merge', [
            'personSn' => $employeeId,
            'imgUrl' => $imageUrl,
            'imgBase64' => $imageBase64,
            'quality' => $photoQuality,
        ]);
    }

    public function findFace(string $employeeId): array
    {
        return $this->post('/face/find', [
            'personSn' => $employeeId,
        ], requireBusinessSuccess: false);
    }

    public function deletePerson(string $employeeId): array
    {
        return $this->post('/person/delete', [
            'sn' => $employeeId,
        ]);
    }

    public function personExists(?array $response): bool
    {
        $data = $response['data'] ?? null;

        if (is_array($data)) {
            return filled($data['sn'] ?? null)
                || filled($data['personSn'] ?? null)
                || filled($data['name'] ?? null);
        }

        return filled($data);
    }

    public function faceExists(?array $response): bool
    {
        $data = $response['data'] ?? null;

        if (is_array($data)) {
            return filled($data['imgBase64'] ?? null)
                || filled($data['featureData'] ?? null);
        }

        return false;
    }

    public function extractPersonList(?array $response): array
    {
        $data = $response['data'] ?? null;

        if (is_array($data) && array_is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        if (! is_array($data)) {
            return [];
        }

        foreach (['list', 'rows', 'records', 'personList', 'dataList', 'items'] as $key) {
            $items = $data[$key] ?? null;

            if (is_array($items) && array_is_list($items)) {
                return array_values(array_filter($items, 'is_array'));
            }
        }

        if (array_key_exists('sn', $data) || array_key_exists('personSn', $data)) {
            return [$data];
        }

        return [];
    }

    public function personListHasMore(?array $response, int $page, int $length, int $itemsCount): bool
    {
        $data = $response['data'] ?? null;

        if (is_array($data)) {
            foreach (['total', 'count', 'totalCount'] as $key) {
                $total = $data[$key] ?? null;

                if (is_numeric($total)) {
                    return ((int) $total) > ($page * $length);
                }
            }

            foreach (['hasNext', 'hasMore', 'nextPage'] as $key) {
                $flag = $data[$key] ?? null;

                if (is_bool($flag)) {
                    return $flag;
                }

                if ($key === 'nextPage' && is_numeric($flag)) {
                    return (int) $flag > $page;
                }
            }
        }

        return $itemsCount >= $length && $itemsCount > 0;
    }

    protected function personPayload(array $person): array
    {
        return [
            'type' => $person['person_type'],
            'sn' => $person['employee_id'],
            'name' => $person['name'],
            'acGroupNumber' => $person['ac_group_number'],
            'verifyStyle' => $person['verify_style'],
        ];
    }

    protected function post(string $path, array $payload = [], bool $requireBusinessSuccess = true): array
    {
        try {
            $response = $this->http()
                ->post($this->endpoint($path), $this->withDeviceCredentials($payload))
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('msg')
                ?? $exception->response?->body()
                ?? $exception->getMessage();

            throw new RuntimeException('Gateway request failed: '.$message, previous: $exception);
        }

        $decoded = $response->json();

        if (! is_array($decoded)) {
            return ['raw' => $response->body()];
        }

        if ($requireBusinessSuccess && ! $this->responseIndicatesSuccess($decoded)) {
            $message = (string) ($decoded['msg'] ?? 'Gateway business request failed.');
            $code = (string) ($decoded['code'] ?? '');
            $suffix = $code !== '' ? " [code: {$code}]" : '';

            throw new RuntimeException('Gateway request failed: '.$message.$suffix);
        }

        return $decoded;
    }

    protected function http(): PendingRequest
    {
        $baseUrl = $this->settings->gatewayBaseUrl();

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('GATEWAY_BASE_URL is not configured.');
        }

        return Http::asForm()->timeout((int) config('gateway.timeout_seconds', 10));
    }

    protected function endpoint(string $path): string
    {
        return rtrim($this->settings->gatewayBaseUrl(), '/').'/'.ltrim($path, '/');
    }

    protected function withDeviceCredentials(array $payload): array
    {
        $deviceKey = $this->deviceContext?->device_key ?: config('gateway.device_key');
        $secret = $this->resolveSecret();

        if (! is_string($deviceKey) || $deviceKey === '') {
            throw new RuntimeException('GATEWAY_DEVICE_KEY is not configured.');
        }

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('GATEWAY_SECRET is not configured.');
        }

        return array_filter([
            'deviceKey' => $deviceKey,
            'secret' => $secret,
            ...$payload,
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    protected function resolveSecret(): mixed
    {
        if (! $this->deviceContext) {
            return config('gateway.secret');
        }

        try {
            $secret = $this->deviceContext->secret;
        } catch (DecryptException $exception) {
            $rawSecret = $this->deviceContext->getRawOriginal('secret');

            if ($this->isLegacyPlaintextSecret($rawSecret)) {
                Log::warning('Using a legacy plaintext device secret for gateway request.', [
                    'device_id' => $this->deviceContext->id,
                    'device_key' => $this->deviceContext->device_key,
                ]);

                return $rawSecret;
            }

            $fallbackSecret = config('gateway.secret');

            if (is_string($fallbackSecret) && $fallbackSecret !== '') {
                Log::warning('Falling back to GATEWAY_SECRET after failing to decrypt the managed device secret.', [
                    'device_id' => $this->deviceContext->id,
                    'device_key' => $this->deviceContext->device_key,
                ]);

                return $fallbackSecret;
            }

            throw new RuntimeException(
                'Stored device secret could not be decrypted with the current APP_KEY. Re-save the device secret in admin and try again.',
                previous: $exception,
            );
        }

        return $secret ?: config('gateway.secret');
    }

    protected function isLegacyPlaintextSecret(mixed $rawSecret): bool
    {
        if (! is_string($rawSecret) || $rawSecret === '') {
            return false;
        }

        $decoded = base64_decode($rawSecret, true);

        if ($decoded === false) {
            return true;
        }

        $payload = json_decode($decoded, true);

        if (! is_array($payload)) {
            return true;
        }

        return ! array_key_exists('iv', $payload)
            || ! array_key_exists('value', $payload)
            || ! array_key_exists('mac', $payload);
    }

    protected function responseIndicatesSuccess(array $response): bool
    {
        if (array_key_exists('success', $response)) {
            return $response['success'] === true || $response['success'] === 'true' || $response['success'] === 1 || $response['success'] === '1';
        }

        $code = (string) ($response['code'] ?? '');

        return in_array($code, ['000', '0', '200'], true);
    }
}
