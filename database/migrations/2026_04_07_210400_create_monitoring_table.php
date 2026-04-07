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
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')
                ->constrained('jadwal')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->enum('status', ['belum_mulai', 'proses', 'selesai', 'tidak_hadir'])
                ->default('belum_mulai');
            $table->text('laporan')->nullable();
            $table->timestamp('dipantau_at')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_id', 'pegawai_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring');
    }
};
