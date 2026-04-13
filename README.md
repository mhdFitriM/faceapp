# FaceApp Stack

This repository is now structured as one deployable stack for a fresh VPS.

## Services

- `frontend`: React/Vite face app
- `api`: Laravel API for enrollment and verification
- `gateway`: legacy Java gateway SDK service
- `caddy`: reverse proxy for HTTPS and domain routing

## Domains

- `FACEAPP_DOMAIN` points to the React app
- `FACEAPP_API_DOMAIN` points to the Laravel API

Both domains should resolve to the same VPS public IP.

The Laravel monitor page lives at `/devices` on the API domain.

## Device Connectivity

The physical face device does not use the website domains for its TCP channel unless the device supports domains explicitly. In most cases, configure the device to connect to:

- VPS public IP
- Port `10010`
- Protocol `TCP`
- Connection mode `cloud`
- Secret = same value as `GATEWAY_SECRET`

The Java gateway container listens on port `10010`, and Docker publishes that port from the VPS.

## Quick Start

1. Copy the root env file:

```bash
cp .env.example .env
```

2. Edit `.env` with your real values:

- `FACEAPP_DOMAIN`
- `FACEAPP_API_DOMAIN`
- `FACEAPP_API_ORIGIN`
- `LARAVEL_APP_NAME`
- `GATEWAY_IMAGE_BASE_URL`
- `GATEWAY_CALLBACK_BASE_URL`
- `LARAVEL_APP_KEY`
- `LARAVEL_SESSION_COOKIE`
- `GATEWAY_DEVICE_KEY`
- `GATEWAY_SECRET`
- `GATEWAY_HEARTBEAT_INTERVAL_SECONDS`
- `GATEWAY_ONLINE_WINDOW_SECONDS`

3. Point both DNS records to your VPS public IP.

4. Open firewall ports on the VPS:

- `80`
- `443`
- `10010`
- `10011`

5. Start the stack:

```bash
docker compose up -d --build
```

## How It Works

1. User opens the frontend on `FACEAPP_DOMAIN`.
2. Frontend sends the captured face photo to Laravel on `FACEAPP_API_DOMAIN`.
3. Laravel stores the image.
4. Laravel calls the gateway on the internal Docker network.
5. Gateway pushes the person and face to the device.
6. Laravel verifies the face using `face/find`.
7. The frontend receives success or failure.

## Monitoring

Open `https://FACEAPP_API_DOMAIN/devices` after deployment.

From this page you can:

- check whether the gateway can currently read the configured device
- see recent heartbeat and access record callbacks
- review enrollment failures
- push the callback URLs back into the device using `device/setSevConfig`

## Important URL Detail

The legacy gateway expects a plain HTTP image URL for `face/merge`, so the stack uses a public plain HTTP image URL for the gateway image fetch:

- `GATEWAY_IMAGE_BASE_URL=http://faceapp-api.example.com/storage`
- `GATEWAY_CALLBACK_BASE_URL=http://faceapp-api.example.com`

At the same time, users access the API publicly over HTTPS:

- `FACE_PUBLIC_BASE_URL=https://your-api-domain/storage`

This split is intentional and required for the old gateway flow. The physical device should be able to reach both `GATEWAY_IMAGE_BASE_URL` and `GATEWAY_CALLBACK_BASE_URL` directly over plain HTTP.

## Main Commands

Build and start:

```bash
docker compose up -d --build
```

View logs:

```bash
docker compose logs -f
```

Stop:

```bash
docker compose down
```
