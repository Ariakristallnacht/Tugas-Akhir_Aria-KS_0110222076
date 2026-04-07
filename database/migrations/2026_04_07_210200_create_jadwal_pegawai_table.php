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
        Schema::create('jadwal_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')
                ->constrained('jadwal')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('peran_tugas', 100)->nullable();
            $table->enum('status_penugasan', ['dijadwalkan', 'hadir', 'izin', 'berhalangan'])
                ->default('dijadwalkan');
            $table->timestamps();

            $table->unique(['jadwal_id', 'pegawai_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pegawai');
    }
};
