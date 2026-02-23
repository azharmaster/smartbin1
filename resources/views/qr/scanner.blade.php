@extends('layouts.app')

@section('content_title', 'QR Scanner')

@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#scannerHelpModal" style="
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #faa70c;
        color: #fff;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        z-index: 999;
    "
    title="QR Scanner Guide">
    ?
</button>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header text-center fw-bold">
                    Scan Asset QR Code
                </div>

                <div class="card-body text-center">

                    {{-- Video element --}}
                    <video id="qr-video" style="width:100%; border-radius:10px;" autoplay muted playsinline></video>

                    {{-- Status messages --}}
                    <div id="scanner-message" class="text-muted my-3">
                        Initializing camera...
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script type="module">
    import QrScanner from 'https://unpkg.com/qr-scanner@1.4.2/qr-scanner.min.js';

    const video = document.getElementById('qr-video');
    const message = document.getElementById('scanner-message');

    async function initScanner() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            message.innerHTML = '<span class="text-danger">No camera detected</span>';
            return;
        }

        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const cameras = devices.filter(d => d.kind === 'videoinput');

            if (cameras.length === 0) {
                message.innerHTML = '<span class="text-danger">No camera detected</span>';
                return;
            }

            const selectedCameraId = cameras[0].deviceId;

            const scanner = new QrScanner(
                video,
                result => {
                    scanner.stop();

                    let redirectUrl = '';

                    try {
                        // Coerce to string first
                        const textResult = typeof result === 'string' ? result : JSON.stringify(result);

                        // Try parsing as JSON
                        const parsed = JSON.parse(textResult);

                        // If the QR contains a `data` field, use that
                        if (parsed && parsed.data) {
                            redirectUrl = parsed.data;
                        } else {
                            redirectUrl = textResult;
                        }

                    } catch (e) {
                        // Not JSON, treat as plain text
                        redirectUrl = typeof result === 'string' ? result : String(result);
                    }

                    // Make sure redirectUrl is a string
                    redirectUrl = String(redirectUrl);

                    // Redirect if looks like URL
                    if (redirectUrl.startsWith('http://') || redirectUrl.startsWith('https://')) {
                        window.location.href = redirectUrl;
                    } else {
                        // Fallback: asset route
                        window.location.href = `/asset/${encodeURIComponent(redirectUrl)}`;
                    }
                },
                {
                    video: { deviceId: selectedCameraId, width: { ideal: 1280 }, height: { ideal: 720 } },
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    maxScansPerSecond: 5
                }
            );

            await scanner.start();
            message.innerHTML = 'Point your camera at the QR code';

        } catch (err) {
            console.error(err);
            if (err.name === 'NotAllowedError') {
                message.innerHTML = '<span class="text-danger">Camera access denied. Please allow camera permissions.</span>';
            } else if (err.name === 'NotFoundError') {
                message.innerHTML = '<span class="text-danger">No camera found on this device.</span>';
            } else {
                message.innerHTML = `<span class="text-danger">Camera error: ${err.message}</span>`;
            }
        }
    }

    initScanner();
</script>

<!-- QR Scanner Help Modal -->
<div class="modal fade" id="scannerHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">QR Scanner – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-info-circle"></i> Purpose</h6>
        <p>
          This page allows you to scan a bin/asset QR code using your device camera.
          Once scanned, the system will automatically redirect you to the asset’s detail page.
        </p>

        <hr>

        <h6><i class="fas fa-camera"></i> How to Use</h6>
        <ul>
          <li>Allow camera permission when prompted by your browser.</li>
          <li>Point your camera directly at the asset QR code.</li>
          <li>Hold steady until the QR code is detected.</li>
          <li>You will be automatically redirected once scanning is successful.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-sync-alt"></i> How It Works</h6>
        <ul>
          <li>The system activates your device camera.</li>
          <li>When a QR code is detected, scanning stops automatically.</li>
          <li>If the QR contains a full URL, you will be redirected to that link.</li>
          <li>If it contains an asset ID or code, the system redirects to the related asset page.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Troubleshooting</h6>
        <ul>
          <li><strong>No camera detected:</strong> Ensure your device has a working camera.</li>
          <li><strong>Camera access denied:</strong> Enable camera permissions in your browser settings.</li>
          <li><strong>QR not scanning:</strong> Improve lighting and ensure the QR code is clear and not damaged.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-lightbulb"></i> Tips</h6>
        <ul>
          <li>Use this feature for quick access to bin details during maintenance or inspection.</li>
          <li>Ensure the QR code sticker on the bin is clean and visible.</li>
        </ul>

      </div>

    </div>
  </div>
</div>
@endsection
