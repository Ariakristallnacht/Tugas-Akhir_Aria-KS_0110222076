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
        Schema::table('pengajuan_dinas', function (Blueprint $table) {
            $table->string('bukti_surat_path')->nullable()->after('keterangan');
            $table->string('bukti_surat_nama')->nullable()->after('bukti_surat_path');
            $table->string('bukti_surat_mime', 100)->nullable()->after('bukti_surat_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_dinas', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_surat_path',
                'bukti_surat_nama',
                'bukti_surat_mime',
            ]);
        });
    }
};
