@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h5 class="mb-0">QR Code Scanner</h5>
                </div>

                <div class="card-body text-center">

                    <p class="text-muted">
                        Upload a QR image from your gallery or downloads
                    </p>

                    <!-- Upload -->
                    <input type="file"
                           id="qrImage"
                           accept="image/*"
                           class="form-control mb-3">

                    <!-- Canvas -->
                    <canvas id="canvas" hidden></canvas>

                    <!-- Result -->
                    <div id="result" class="mt-3"></div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- jsQR Library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
document.getElementById('qrImage').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const img = new Image();
    const reader = new FileReader();
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    reader.onload = function () {
        img.src = reader.result;
    };

    img.onload = function () {
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, canvas.width, canvas.height);

        if (code) {
            document.getElementById('result').innerHTML = `
                <div class="alert alert-success">
                    QR detected! Redirecting...
                </div>
            `;
            window.location.href = code.data;
        } else {
            document.getElementById('result').innerHTML = `
                <div class="alert alert-danger">
                    No QR code detected in this image.
                </div>
            `;
        }
    };

    reader.readAsDataURL(file);
});
</script>
@endsection
