<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    /** @use HasFactory<\Database\Factories\MeetingFactory> */
    use HasFactory;
    protected $guarded = ['id'];

    // Relasi ke subject (mata pelajaran)
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Relasi ke attendances (absensi siswa di pertemuan ini)
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
