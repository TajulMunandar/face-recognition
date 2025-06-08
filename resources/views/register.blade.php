<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Daftar Wajah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        #preview-container video {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
        }

        .captured-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
        }

        .hint-text {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Absensi Wajah</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="/register" class="nav-link {{ request()->is('register') ? 'active' : '' }}">
                            <i class="bi bi-person-plus-fill me-1"></i> Daftar Wajah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/absen" class="nav-link {{ request()->is('absen') ? 'active' : '' }}">
                            <i class="bi bi-clipboard-check-fill me-1"></i> Absensi
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Form Pendaftaran Wajah</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        <form id="registerForm" action="/register" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ambil Gambar dari Kamera</label>
                                <div id="preview-container" class="mb-2">
                                    <video id="camera" autoplay playsinline></video>
                                </div>
                                <p id="hint" class="hint-text text-center mb-2">Silakan ambil foto (3 kali)</p>
                                <button id="captureBtn" type="button" class="btn btn-outline-primary w-100 mb-3"
                                    onclick="captureImage()">Ambil Gambar</button>
                                <div id="captured-images" class="row g-2"></div>
                            </div>

                            <!-- Hidden Inputs for base64 Images -->
                            <input type="hidden" name="image_0" id="image_0">
                            <input type="hidden" name="image_1" id="image_1">
                            <input type="hidden" name="image_2" id="image_2">

                            <button type="submit" class="btn btn-success w-100 mt-3">Daftar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS -->
    <script>
        const video = document.getElementById('camera');
        const capturedImages = document.getElementById('captured-images');
        const hint = document.getElementById('hint');
        const captureBtn = document.getElementById('captureBtn');
        let currentIndex = 0;

        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(stream => video.srcObject = stream)
            .catch(err => alert('Tidak bisa mengakses kamera: ' + err.message));

        function captureImage() {
            if (currentIndex >= 3) return;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = canvas.toDataURL('image/jpeg');
            document.getElementById('image_' + currentIndex).value = imageData;

            const col = document.createElement('div');
            col.className = 'col-4';
            const img = document.createElement('img');
            img.src = imageData;
            img.className = 'captured-img img-thumbnail';
            col.appendChild(img);
            capturedImages.appendChild(col);

            currentIndex++;

            if (currentIndex < 3) {
                hint.textContent = `Silakan ambil foto (${3 - currentIndex} kali lagi)`;
            } else {
                hint.textContent = 'Semua gambar sudah diambil.';
                captureBtn.disabled = true;
                captureBtn.classList.add('disabled');
            }
        }
    </script>

</body>

</html>
