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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan', 200);
            $table->enum('jenis', ['layanan', 'dinas_luar']);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->date('tanggal');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi', 200);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'terjadwal', 'berjalan', 'selesai', 'dibatalkan'])
                ->default('draft');
            $table->timestamps();

            $table->index(['tanggal', 'status']);
        });

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

        Schema::create('pengajuan_dinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('tujuan', 200);
            $table->text('kegiatan');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'dibatalkan'])
                ->default('diajukan');
            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();

            $table->index(['status', 'tanggal_mulai', 'tanggal_selesai']);
        });

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

        Schema::create('laporan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')
                ->constrained('jadwal')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->date('tanggal');
            $table->text('laporan');
            $table->enum('status_verifikasi', ['menunggu', 'diterima', 'revisi'])
                ->default('menunggu');
            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'status_verifikasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kegiatan');
        Schema::dropIfExists('monitoring');
        Schema::dropIfExists('pengajuan_dinas');
        Schema::dropIfExists('jadwal_pegawai');
        Schema::dropIfExists('jadwal');
        Schema::dropIfExists('kegiatan');
    }
};
