<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('jadwal_pegawai', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('pengajuan_dinas', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('monitoring', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->dropForeign(['pengajuan_dinas_id']);
            $table->foreign('pengajuan_dinas_id')
                ->references('id')
                ->on('pengajuan_dinas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('jadwal_pegawai', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('pengajuan_dinas', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('monitoring', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->foreign('pegawai_id')
                ->references('id')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->dropForeign(['pengajuan_dinas_id']);
            $table->foreign('pengajuan_dinas_id')
                ->references('id')
                ->on('pengajuan_dinas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
};
