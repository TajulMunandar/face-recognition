<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FaceController extends Controller
{
    public function registerForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'image_0' => 'required',
            'image_1' => 'required',
            'image_2' => 'required',
        ]);

        $label = Str::slug($request->name);
        $userDir = public_path("storage/dataset_faces/$label");

        if (File::exists($userDir)) {
            File::deleteDirectory($userDir);
        }
        File::makeDirectory($userDir, 0755, true, true);

        DB::table('users')->updateOrInsert(
            ['face_label' => $label],
            [
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 2, // role siswa
                'face_label' => $label,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        foreach (['image_0', 'image_1', 'image_2'] as $index => $key) {
            $base64Image = $request->input($key);
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            file_put_contents("$userDir/{$label}_$index.jpg", $imageData);
        }

        try {
            $response = Http::post('http://localhost:5000/train');  // **tanpa data apapun**
            if ($response->successful()) {
                return back()->with('success', 'Pendaftaran berhasil, wajah telah diperbarui dan model dilatih ulang.');
            } else {
                return back()->with('error', 'Pendaftaran berhasil tapi gagal melatih ulang model.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Pendaftaran berhasil tapi gagal menghubungi server pelatihan: ' . $e->getMessage());
        }
    }


    public function absenForm(Meeting $meeting)
    {
        $absensi = Attendance::where('meeting_id', $meeting->id)
            ->whereDate('created_at', today())
            ->with('user')
            ->get();
        $users = DB::table('users')->get(); // Fetch all users for the view
        return view('absen', [
            'absensi' => $absensi,
            'meeting' => $meeting,
            'subject' => $meeting->subject,
            'users' => $users,
        ]);
    }

    public function absen(Request $request)
    {
        $request->validate([
            'captured' => 'required|string',
            'meeting' => 'required',
        ]);

        $meeting = Meeting::with('subject')->findOrFail($request->meeting);
        $subject = $meeting->subject;

        // Decode base64 image
        $image = $request->captured;
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        if ($imageData === false) {
            return back()
                ->with('error', 'Gambar tidak valid. Silakan ambil ulang.')
                ->with('error_detail', 'Base64 decode gagal');
        }

        // Simpan file temporer
        $filename = uniqid() . '.jpg';
        $tempPath = storage_path('app/temp/' . $filename);
        File::ensureDirectoryExists(storage_path('app/temp'));
        file_put_contents($tempPath, $imageData);

        // Kirim ke server Python untuk prediksi wajah
        $response = Http::attach(
            'image',
            file_get_contents($tempPath),
            $filename
        )->post('http://127.0.0.1:5000/predict');

        // Hapus file setelah dikirim
        unlink($tempPath);

        if ($response->successful()) {
            $userId = Auth::id() ?? null;
            if (!$userId) {
                return back()
                    ->with('error', 'Sesi login berakhir. Silakan login ulang.')
                    ->with('error_detail', 'Auth::id() kosong');
            }

            if ($userId) {
                // Cek apakah sudah absen hari ini
                $already = DB::table('attendances')
                    ->where('meeting_id', $meeting->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (!$already) {
                    $startTime = \Carbon\Carbon::parse($subject->start_time);
                    $now = now();
                    $status = $now->lessThanOrEqualTo($startTime) ? 'pending' : 'terlambat';

                    // simpan foto hasil capture
                    $photoName = uniqid() . '.jpg';
                    $photoPath = 'attendances/' . $photoName;
                    Storage::disk('public')->put($photoPath, $imageData);

                    DB::table('attendances')->insert([
                        'user_id'    => $userId,
                        'meeting_id' => $meeting->id,
                        'absen_at'   => now(),
                        'status'     => $status,
                        'photo'      => $photoPath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                return redirect()->back()->with('success', 'Absensi berhasil!');
            } else {
                return redirect()->back()->with('error', 'Wajah tidak dikenali!');
            }
        }
        
        if ($response->status() === 401) {
            $result = $response->json();
            return back()->with('error', $result['message'] ?? 'Wajah tidak dikenali');
        }

        return redirect()->back()->with('error', 'Wajah tidak dikenali!');
    }
}
