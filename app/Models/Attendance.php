<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendaceFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke user (siswa yang absen)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke meeting (pertemuan tertentu)
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
