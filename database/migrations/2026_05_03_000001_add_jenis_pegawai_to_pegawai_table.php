<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->string('jenis_pegawai', 20)->default('asn')->after('nip');
        });

        DB::table('pegawai')
            ->whereNull('jenis_pegawai')
            ->update(['jenis_pegawai' => 'asn']);
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('jenis_pegawai');
        });
    }
};
