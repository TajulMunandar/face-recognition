<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


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
            ['name' => $request->name, 'face_label' => $label]
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


    public function absenForm()
    {
        $absensi = Attendance::all();
        return view('absen')->with('absensi', $absensi);
    }

    public function absen(Request $request)
    {
        $request->validate([
            'captured' => 'required|string',
        ]);

        // Decode base64 image
        $image = $request->captured;
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

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
            $result = $response->json();
            $userId = $result['user_id'] ?? null;

            if ($userId) {
                // Cek apakah sudah absen hari ini
                $already = DB::table('attendances')
                    ->whereDate('created_at', now()->toDateString())
                    ->where('user_id', $userId)
                    ->exists();

                if (!$already) {
                    DB::table('attendances')->insert([
                        'user_id' => $userId,
                        'absen_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return redirect()->back()->with('success', 'Absensi berhasil!');
            } else {
                return redirect()->back()->with('error', 'Wajah tidak dikenali!');
            }
        }

        return redirect()->back()->with('error', 'Wajah tidak dikenali!');
    }
}
