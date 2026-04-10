# FaceApp Device Flow

## What This Backend Does

This Laravel app accepts a captured face photo from the React `faceapp`, stores the image, calls the legacy gateway HTTP API, and verifies the upload by querying the device back.

## API Endpoints

- `GET /api/device/status`
- `POST /api/enrollments`
- `GET /api/enrollments/{public_id}`

## Enrollment Request Body

```json
{
  "employee_id": "EMP-4829",
  "name": "Alexandra Chen",
  "photo_data_url": "data:image/jpeg;base64,..."
}
```

Optional fields:

- `person_type`
- `verify_style`
- `ac_group_number`
- `photo_quality`

## Required Environment Variables

- `GATEWAY_BASE_URL`
- `GATEWAY_DEVICE_KEY`
- `GATEWAY_SECRET`
- `APP_URL`

Set `APP_URL` to the actual URL the gateway/device can reach, not `localhost`.

If you want a dedicated public image base URL, set:

- `FACE_PUBLIC_BASE_URL`

Example:

```env
APP_URL=http://192.168.1.50:8000
FACE_PUBLIC_BASE_URL=http://192.168.1.50:8000/storage
GATEWAY_BASE_URL=http://127.0.0.1:8190/api
GATEWAY_DEVICE_KEY=YOUR_DEVICE_KEY
GATEWAY_SECRET=YOUR_DEVICE_SECRET
```

## Local Test Setup Order

1. Install Java 8 and set `JAVA_HOME`.
2. Start the legacy gateway service from `gateway-sdk-service_v2.0.6_20240117\start.bat`.
3. Confirm port `8190` is up.
4. Run Laravel with a LAN-reachable host:

```powershell
cd c:\Users\admin\Desktop\faceapp_main\faceapp-api
php artisan serve --host=0.0.0.0 --port=8000
```

5. Run the React app:

```powershell
cd c:\Users\admin\Desktop\faceapp_main\faceapp
npm run dev
```

6. On the face device communication settings:
   - Server IP: your PC LAN IP
   - Server Port: `10010`
   - Connection Mode: cloud
   - Protocol: TCP
   - Secret: same value as `GATEWAY_SECRET`

7. In Laravel `.env`:
   - `APP_URL=http://YOUR_PC_IP:8000`
   - `FACE_PUBLIC_BASE_URL=http://YOUR_PC_IP:8000/storage`
   - `GATEWAY_BASE_URL=http://127.0.0.1:8190/api`
   - `GATEWAY_DEVICE_KEY=your actual device key`
   - `GATEWAY_SECRET=the same secret used on device`

8. Test backend connectivity:

```powershell
curl http://127.0.0.1:8000/api/device/status
```

## Expected Enrollment Flow

1. React sends `employee_id`, `name`, and `photo_data_url` to Laravel.
2. Laravel saves the image into public storage.
3. Laravel calls `person/find`.
4. Laravel calls `person/create` or `person/update`.
5. Laravel calls `face/merge`.
6. Laravel calls `face/find` to verify the upload.
7. Laravel returns `verified` or `failed`.
