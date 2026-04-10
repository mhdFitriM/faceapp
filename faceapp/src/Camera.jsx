import { useState, useRef, useCallback, useEffect } from 'react'
import './Camera.css'

const FACING_MODES = { USER: 'user', ENVIRONMENT: 'environment' }

function isLocalCameraHost(hostname) {
  return hostname === 'localhost'
    || hostname === '127.0.0.1'
    || hostname === '[::1]'
    || hostname.endsWith('.localhost')
}

function canUseCameraApi() {
  return typeof navigator !== 'undefined' && typeof navigator.mediaDevices?.getUserMedia === 'function'
}

function hasSecureCameraContext() {
  if (typeof window === 'undefined') {
    return false
  }

  return window.isSecureContext || isLocalCameraHost(window.location.hostname)
}

function buildCameraConstraints(mode) {
  return [
    {
      video: {
        facingMode: { ideal: mode },
        width: { ideal: 1280 },
        height: { ideal: 720 },
      },
      audio: false,
    },
    {
      video: {
        facingMode: { ideal: mode },
      },
      audio: false,
    },
    {
      video: true,
      audio: false,
    },
  ]
}

function buildCameraError(err) {
  if (!canUseCameraApi()) {
    return 'This browser does not support camera access. Use the latest Chrome, Edge, Safari, or Firefox.'
  }

  if (!hasSecureCameraContext()) {
    return 'Desktop browsers only allow camera access on HTTPS or localhost. Open FaceApp on HTTPS and try again.'
  }

  switch (err?.name) {
    case 'NotAllowedError':
    case 'SecurityError':
      return 'Camera access is blocked. Allow camera permission in your browser settings, then try again.'
    case 'NotFoundError':
      return 'No camera was found on this device. Connect a webcam and try again.'
    case 'NotReadableError':
    case 'AbortError':
      return 'The camera is busy in another app or browser tab. Close the other app and try again.'
    case 'OverconstrainedError':
      return 'This camera does not support the preferred mode. Try again to use the default camera.'
    default:
      return 'Unable to start the camera right now. Try again in a moment.'
  }
}

export default function Camera({ onCapture, onClose }) {
  const videoRef = useRef(null)
  const canvasRef = useRef(null)
  const streamRef = useRef(null)

  const [facing, setFacing] = useState(FACING_MODES.USER)
  const [ready, setReady] = useState(false)
  const [starting, setStarting] = useState(false)
  const [awaitingStart, setAwaitingStart] = useState(false)
  const [countdown, setCountdown] = useState(null)
  const [flash, setFlash] = useState(false)
  const [error, setError] = useState(null)
  const [scanning, setScanning] = useState(false)

  const stopCamera = useCallback(() => {
    if (streamRef.current) {
      streamRef.current.getTracks().forEach((track) => track.stop())
      streamRef.current = null
    }

    if (videoRef.current) {
      videoRef.current.srcObject = null
    }

    setReady(false)
  }, [])

  const startCamera = useCallback(async (mode) => {
    if (!canUseCameraApi() || !hasSecureCameraContext()) {
      setAwaitingStart(false)
      setError(buildCameraError())
      return
    }

    setStarting(true)
    setAwaitingStart(false)
    setError(null)

    stopCamera()

    let lastError = null

    for (const constraints of buildCameraConstraints(mode)) {
      try {
        const stream = await navigator.mediaDevices.getUserMedia(constraints)
        streamRef.current = stream

        if (videoRef.current) {
          const video = videoRef.current
          video.srcObject = stream

          await new Promise((resolve) => {
            if (video.readyState >= 1) {
              resolve()
              return
            }

            video.onloadedmetadata = () => resolve()
          })

          try {
            await video.play()
          } catch (playError) {
            console.warn('Camera preview play() was blocked by the browser.', playError)
          }
        }

        setReady(true)
        setStarting(false)
        return
      } catch (err) {
        lastError = err

        if (err?.name === 'NotAllowedError' || err?.name === 'SecurityError' || err?.name === 'NotReadableError') {
          break
        }
      }
    }

    stopCamera()
    setStarting(false)
    setError(buildCameraError(lastError))
    console.error(lastError)
  }, [stopCamera])

  useEffect(() => {
    let cancelled = false

    const prepareCamera = async () => {
      if (!canUseCameraApi() || !hasSecureCameraContext()) {
        setError(buildCameraError())
        return
      }

      if (typeof navigator.permissions?.query === 'function') {
        try {
          const permission = await navigator.permissions.query({ name: 'camera' })

          if (cancelled) {
            return
          }

          if (permission.state === 'granted') {
            startCamera(FACING_MODES.USER)
            return
          }

          if (permission.state === 'denied') {
            setError(buildCameraError({ name: 'NotAllowedError' }))
            return
          }
        } catch (err) {
          console.warn('Camera permission state could not be queried.', err)
        }
      }

      if (!cancelled) {
        setAwaitingStart(true)
      }
    }

    prepareCamera()

    return () => {
      cancelled = true
      stopCamera()
    }
  }, [startCamera, stopCamera])

  const flipCamera = () => {
    const newMode = facing === FACING_MODES.USER ? FACING_MODES.ENVIRONMENT : FACING_MODES.USER
    setFacing(newMode)
    startCamera(newMode)
  }

  const triggerCapture = () => {
    if (!ready || countdown !== null) return
    setCountdown(3)
    let c = 3
    const interval = setInterval(() => {
      c -= 1
      setCountdown(c)
      if (c === 0) {
        clearInterval(interval)
        setCountdown(null)
        capturePhoto()
      }
    }, 1000)
  }

  const capturePhoto = () => {
    if (!videoRef.current || !canvasRef.current) return
    const video = videoRef.current
    const canvas = canvasRef.current
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    const ctx = canvas.getContext('2d')

    if (facing === FACING_MODES.USER) {
      ctx.translate(canvas.width, 0)
      ctx.scale(-1, 1)
    }
    ctx.drawImage(video, 0, 0)

    setFlash(true)
    setTimeout(() => setFlash(false), 300)

    setScanning(true)
    setTimeout(() => {
      setScanning(false)
      const dataUrl = canvas.toDataURL('image/jpeg', 0.92)
      onCapture(dataUrl)
    }, 2200)
  }

  const hintText = ready
    ? 'Position your face within the frame'
    : 'Use HTTPS or localhost on desktop, then allow camera access when prompted'

  return (
    <div className="camera-overlay animate-slideUp">
      <div className="camera-header glass">
        <button className="cam-icon-btn" onClick={onClose} aria-label="Close camera">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>
        <span className="cam-title">Face Capture</span>
        <button className="cam-icon-btn" onClick={flipCamera} aria-label="Flip camera" disabled={!ready}>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M1 4v6h6M23 20v-6h-6" />
            <path d="M20.49 9A9 9 0 005.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 013.51 15" />
          </svg>
        </button>
      </div>

      <div className="camera-viewport">
        {error ? (
          <div className="cam-error">
            <div className="cam-error-icon">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" strokeWidth="2" strokeLinecap="round" />
              </svg>
            </div>
            <p>{error}</p>
            <button className="btn-primary" onClick={() => startCamera(facing)}>Try Again</button>
          </div>
        ) : (
          <>
            <video
              ref={videoRef}
              className={`cam-video ${facing === FACING_MODES.USER ? 'mirror' : ''}`}
              muted
              playsInline
              autoPlay
            />

            {flash && <div className="cam-flash" />}

            <div className={`face-guide ${ready ? 'visible' : ''}`}>
              <div className="guide-corner tl" />
              <div className="guide-corner tr" />
              <div className="guide-corner bl" />
              <div className="guide-corner br" />
              {scanning && <div className="scan-line" />}
            </div>

            {countdown !== null && (
              <div className="cam-countdown animate-fadeIn">
                <span>{countdown}</span>
              </div>
            )}

            {scanning && (
              <div className="cam-scanning animate-fadeIn">
                <div className="scanning-dots">
                  <span /><span /><span />
                </div>
                <p>Analyzing face...</p>
              </div>
            )}

            {awaitingStart && !starting && !ready && (
              <div className="cam-loading cam-prompt">
                <div className="cam-permission-icon">
                  <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                    <circle cx="12" cy="13" r="4" />
                  </svg>
                </div>
                <p>Tap below to let this browser open your camera.</p>
                <button className="btn-primary" onClick={() => startCamera(facing)}>
                  Open Camera
                </button>
              </div>
            )}

            {starting && !ready && !error && (
              <div className="cam-loading">
                <div className="cam-spinner" />
                <p>Initializing camera...</p>
              </div>
            )}
          </>
        )}
        <canvas ref={canvasRef} style={{ display: 'none' }} />
      </div>

      <p className="cam-hint">{hintText}</p>

      <div className="camera-controls">
        <button
          id="shutter-btn"
          className={`shutter-btn ${!ready || countdown !== null || scanning ? 'disabled' : ''}`}
          onClick={triggerCapture}
          disabled={!ready || countdown !== null || scanning}
          aria-label="Take photo"
        >
          <div className="shutter-inner" />
          <div className="shutter-ring" />
        </button>
      </div>
    </div>
  )
}
