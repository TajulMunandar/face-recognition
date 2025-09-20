<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::orderBy('start_time')->get();
        return view('subjects', compact('subjects'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'sks' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $subject = Subject::create($request->only(['name', 'start_time', 'end_time', 'sks']));

            $jumlahPertemuan = $request->sks * 16;

            for ($i = 1; $i <= $jumlahPertemuan; $i++) {
                Meeting::create([
                    'subject_id' => $subject->id,
                    'pertemuan_ke' => $i,
                    'tanggal' => now()->addWeeks($i - 1), // contoh auto-generate tanggal tiap minggu
                ]);
            }

            DB::commit();

            return redirect()->route('subjects.index')->with('success', 'Subject berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('subjects.index')->with('error', 'Gagal menambahkan subject: ' . $e->getMessage());
        }
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
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $subject->update($request->all());
        return redirect()->route('subjects.index')->with('success', 'Subject berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        $subject->meetings()->delete();

        // baru hapus subject
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'Subject berhasil dihapus');
    }
}
