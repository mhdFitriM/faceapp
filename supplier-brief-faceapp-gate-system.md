# FaceApp Gate System Supplier Brief

## Objective

We need a gate access system that can integrate with our `FaceApp` using simple HTTP APIs.

Our preferred architecture is a fully cloud-managed platform, so we can access and manage devices remotely without depending on local LAN setup at each site.

The main goal is:

- A user can capture or upload a face photo in `FaceApp`
- `FaceApp` can register or update that user on the gate device
- The face template/photo is sent to the gate device
- The user can then use face recognition to open the gate

The new solution should provide functionality similar to our old gateway SDK service, but in a simpler and maintainable way.

## What We Need

The new supplier's system must support these core functions:

- Preferably fully cloud-based integration using HTTP API
- Centralized cloud platform to manage multiple devices remotely
- Devices grouped by client, branch, or store
- `FaceApp` should be able to assign or sync users to the correct branch/store device(s)
- Avoid LAN-only setup if possible
- If LAN or local gateway is required, it must be lightweight and fully documented

## Required Business Flow

1. User opens `FaceApp`
2. User captures or uploads a face photo
3. `FaceApp` sends the user profile and face photo to the gate system through cloud API
4. Gate system creates or updates the person on the device
5. Gate system uploads the face data to the device
6. Device confirms success or returns a clear error
7. User can immediately use face recognition at the gate

If cloud-to-device delivery is not supported, then a secondary option is local LAN integration.

## Minimum API Capabilities Required

The supplier should provide HTTP endpoints or equivalent API functions for:

- Get device status and device information
- Register person
- Update person
- Delete person
- Upload or update face photo for a person
- Delete face photo for a person
- Query whether a person/face exists on the device
- Open gate remotely
- Get attendance/access/recognition records
- Push event callbacks or webhook notifications for access records and device heartbeat
- Map devices by client / branch / store in the cloud platform

## Data We Need To Send

At minimum, the API should support:

- `deviceId` or `deviceKey`
- `personId` or employee number
- Person name
- Person type if needed
- Face image from `FaceApp`
- Face image input as one of these:
  - image file upload
  - base64 image
  - local LAN image URL

## Important Functional Requirements

- Face enrollment must be possible from our app, not only from the device screen
- Prefer cloud-based device access and management
- We want to manage devices remotely by client, branch, or store
- LAN-only architecture is not preferred unless cloud is not possible
- The API must return clear success/failure responses
- The API must support create and update flows
- The API must be stable enough for production use and future support
- The supplier must provide API documentation and sample requests/responses

## Nice To Have

- Device heartbeat callback
- Access logs with snapshot image
- Real-time access event notification
- Access group / time zone support
- Multiple gate devices under one API

## Technical Note From Legacy System

Our previous setup exposed HTTP APIs for device management, person management, face upload, record retrieval, and remote door opening. It also supported callback/reporting for heartbeat and access records. We want the new solution to provide the same practical capabilities, but in a cleaner and better-supported product.

## Expected Supplier Deliverables

- API documentation
- Example integration flow for person + face enrollment
- Supported image format/size requirements
- Network architecture diagram
- Explanation of cloud architecture and how devices connect to cloud
- Method to organize devices by client / branch / store
- Error code list
- Device model compatibility list
- Deployment method for LAN environment

## Simple Requirement Summary To Send Supplier

We need a gate access system that our `FaceApp` can integrate with using HTTP API. Preferably, the solution should be fully cloud-based so we can remotely manage devices without relying on local LAN setup at each site. We want devices to be organized by client, branch, or store, so our app can send each user's face enrollment to the correct location. From our app, we must be able to create/update a person, upload that person's face photo, confirm enrollment success, retrieve access logs, and optionally open the gate remotely. If cloud architecture is not possible, then LAN or a local gateway can be considered as a secondary option, but it must be lightweight, stable, and fully documented.
