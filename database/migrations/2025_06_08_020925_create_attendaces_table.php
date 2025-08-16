<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamp('absen_at')->nullable(); // Tambahkan jika ingin waktu absen
            $table->string('status')->default('hadir'); // hadir, terlambat, izin, sakit, alfa
            $table->unsignedBigInteger('subject_id')->nullable(); // mata pelajaran
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendaces');
    }
};
