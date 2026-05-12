<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->enum('jenis_kegiatan', ['layanan', 'dinas_luar'])
                ->default('layanan')
                ->after('pegawai_id');
            $table->foreignId('pengajuan_dinas_id')
                ->nullable()
                ->after('jadwal_id')
                ->constrained('pengajuan_dinas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        DB::table('laporan_kegiatan')
            ->whereNull('jenis_kegiatan')
            ->update(['jenis_kegiatan' => 'layanan']);

        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->foreignId('jadwal_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->foreignId('jadwal_id')
                ->nullable(false)
                ->change();
            $table->dropConstrainedForeignId('pengajuan_dinas_id');
            $table->dropColumn('jenis_kegiatan');
        });
    }
};
