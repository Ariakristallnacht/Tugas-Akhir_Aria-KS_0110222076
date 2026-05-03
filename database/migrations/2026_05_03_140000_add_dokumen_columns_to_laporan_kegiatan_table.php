<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->string('dokumen_laporan_path')->nullable()->after('laporan');
            $table->string('dokumen_laporan_nama')->nullable()->after('dokumen_laporan_path');
            $table->string('dokumen_laporan_mime', 100)->nullable()->after('dokumen_laporan_nama');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropColumn([
                'dokumen_laporan_path',
                'dokumen_laporan_nama',
                'dokumen_laporan_mime',
            ]);
        });
    }
};
