<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::orderBy('start_time')->get();

        return view('index', compact('subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:izin,sakit,alfa',
        ]);

        $userId = $request->user_id; // ambil dari form

        // cek apakah user sudah punya absensi hari ini
        $attendance = DB::table('attendances')
            ->where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->where('subject_id', $request->subject_id)
            ->first();

        if ($attendance) {
            // update status
            DB::table('attendances')->where('id', $attendance->id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
        } else {
            // insert baru
            DB::table('attendances')->insert([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Status absensi berhasil diperbarui.');
    }
}
