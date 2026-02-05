@extends('layouts.app')

@section('content_title', 'QR Scanner')

@section('content')
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

@endsection
