<!DOCTYPE html>
<html>

<head>
    <title>Rekap Absensi {{ $subject->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        h3 {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <h3>Rekap Absensi Mata Kuliah: {{ $subject->name }}</h3>
    <p>Jam: {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} -
        {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}</p>

    @foreach ($subject->meetings as $meeting)
        <h4>Pertemuan: {{ $meeting->title ?? 'Pertemuan ' . $meeting->id }}</h4>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mahasiswa</th>
                    <th>Status</th>
                    <th>Waktu Absen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meeting->attendances as $i => $attendance)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ ucfirst($attendance->status) }}</td>
                        <td>{{ $attendance->absen_at ? \Carbon\Carbon::parse($attendance->absen_at)->format('d-m-Y H:i') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada absensi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>

</html>
