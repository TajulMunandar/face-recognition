<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Absensi Wajah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        video {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 0.25rem;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 0.25rem;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Absensi Wajah</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @if (auth()->user()->role == 1)
                        <li class="nav-item">
                            <a href="/register" class="nav-link {{ request()->is('register') ? 'active' : '' }}">
                                <i class="bi bi-person-plus-fill me-1"></i> Daftar Wajah
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="/" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                            <i class="bi bi-clipboard-check-fill me-1"></i> Absensi
                        </a>
                    </li>
                    @if (auth()->user()->role == 1)
                        <li class="nav-item">
                            <a href="{{ route('subjects.index') }}"
                                class="nav-link {{ request()->is('subjects') ? 'active' : '' }}">
                                <i class="bi bi-journal-text me-1"></i> Mata Pelajaran
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <form action="/logout" method="POST">
                            @csrf
                            <button type="submit" class="nav-link">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container py-4">
        <!-- Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary">
                <i class="bi bi-clipboard-check-fill me-2"></i>Daftar Absensi Hari Ini MP:{{ $subject->name }}
            </h4>
            <div class="row">
                <div class="col">
                    <button type="button" class="btn btn-success shadow-sm me-2" data-bs-toggle="modal"
                        data-bs-target="#absenModal">
                        <i class="bi bi-person-check-fill me-1"></i>Absen Sekarang
                    </button>
                    <button type="button" class="btn btn-warning shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#statusModal">
                        <i class="bi bi-person-check-fill me-1"></i>Update Status
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Table -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover table-bordered align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Waktu Absen</th>
                        <th scope="col">Status</th>
                        <th scope="col">Photo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $i => $a)
                        <tr>
                            <td class="fw-semibold">{{ $i + 1 }}</td>
                            <td>{{ $a->user->name }}</td>
                            <td>
                                @if ($a->absen_at)
                                    <span
                                        class="badge bg-success">{{ \Carbon\Carbon::parse($a->absen_at)->format('H:i:s, d M Y') }}</span>
                                @else
                                    <span class="text-muted">Belum Absen</span>
                                @endif
                            </td>
                            <td>
                                @switch($a->status)
                                    @case('hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @break

                                    @case('terlambat')
                                        <span class="badge bg-warning text-dark">Terlambat</span>
                                    @break

                                    @case('izin')
                                        <span class="badge bg-info text-dark">Izin</span>
                                    @break

                                    @case('pending')
                                        <span class="badge bg-info text-dark">Pending</span>
                                    @break

                                    @case('sakit')
                                        <span class="badge bg-primary">Sakit</span>
                                    @break

                                    @case('alfa')
                                        <span class="badge bg-danger">Alfa</span>
                                    @break
                                @endswitch
                            </td>
                            <td>
                                <img src="{{ asset('storage/' . $a->photo) }}" alt="Photo" class="img-fluid rounded"
                                    style="max-width: 100px;">
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada data absensi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Absensi -->
        <div class="modal fade" id="absenModal" tabindex="-1" aria-labelledby="absenModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="absenModalLabel"><i class="bi bi-camera-fill me-1"></i> Ambil Wajah
                            untuk Absen</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <video id="camera" class="rounded shadow-sm border" autoplay playsinline
                            style="width: 100%; max-height: 400px;"></video>
                        <form id="absenForm" action="/absen" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" name="captured" id="captured">
                            <input type="hidden" name="meeting" value="{{ $meeting->id }}">
                            <button type="button" class="btn btn-primary w-100" onclick="captureAndSubmit()">
                                <i class="bi bi-check-circle-fill me-1"></i>Konfirmasi Absen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Update Status -->
        <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="statusModalLabel"><i class="bi bi-clipboard-check me-1"></i> Ubah
                            Status Absensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('absen.updateStatus') }}" method="POST">
                            @csrf
                            <input type="hidden" name="meeting_id" value="{{ $meeting->id }}">

                            <div class="mb-3">
                                <label for="user_id" class="form-label">Pilih Siswa</label>
                                <select name="user_id" id="user_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Pilih Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="hadir">Hadir</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="alfa">Alfa</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-check-circle me-1"></i> Simpan Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap 5 JS Bundle (dengan Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


        <!-- Script -->
        <script>
            const video = document.getElementById('camera');
            const capturedInput = document.getElementById('captured');

            navigator.mediaDevices.getUserMedia({
                video: true
            }).then(stream => {
                video.srcObject = stream;
            });

            function captureAndSubmit() {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = canvas.toDataURL('image/jpeg');
                capturedInput.value = imageData;
                document.getElementById('absenForm').submit();
            }
        </script>

    </body>

    </html>
