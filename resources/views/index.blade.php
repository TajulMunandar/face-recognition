<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pilih Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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

    <!-- Content -->
    <div class="container py-4">
        <h4 class="fw-bold text-primary mb-4">
            <i class="bi bi-journal-text me-2"></i> Pilih Mata Pelajaran
        </h4>

        <div class="row">
            @forelse($subjects as $subject)
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">{{ $subject->name }}</h5>
                            <p class="text-muted mb-2">
                                {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                            </p>
                            <div class="row">
                                <div class="col col-lg-10">
                                    <button class="btn btn-success w-100" data-bs-toggle="modal"
                                        data-bs-target="#meetingModal{{ $subject->id }}">
                                        <i class="bi bi-person-check-fill me-1"></i> Absen Sekarang
                                    </button>
                                </div>
                                <div class="col col-lg-2">
                                    <a href="{{ route('attendance.subject.download', $subject->id) }}"
                                        class="btn btn-danger w-100">
                                        <i class="bi bi-download me-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="meetingModal{{ $subject->id }}" tabindex="-1"
                    aria-labelledby="meetingModalLabel{{ $subject->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable"> <!-- scrollable -->
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="meetingModalLabel{{ $subject->id }}">
                                    Pilih Pertemuan - {{ $subject->name }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    @foreach ($subject->meetings as $meeting)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pertemuan {{ $meeting->pertemuan_ke }}
                                            @php
                                                $sudahAbsen = $meeting
                                                    ->attendances()
                                                    ->where('user_id', auth()->id())
                                                    ->exists();
                                            @endphp

                                            @if ($sudahAbsen)
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="bi bi-check2-circle me-1"></i> Sudah Absen
                                                </button>
                                            @else
                                                <a href="{{ route('absen.show', $meeting->id) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-circle me-1"></i> Pilih
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning">Belum ada mata pelajaran.</div>
                </div>
            @endforelse
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
