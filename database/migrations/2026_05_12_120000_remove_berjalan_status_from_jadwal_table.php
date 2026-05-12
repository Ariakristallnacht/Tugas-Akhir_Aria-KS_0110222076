<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('jadwal')
            ->where('status', 'berjalan')
            ->update(['status' => 'terjadwal']);

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE jadwal
            MODIFY status ENUM('draft', 'terjadwal', 'selesai', 'dibatalkan')
            NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE jadwal
            MODIFY status ENUM('draft', 'terjadwal', 'berjalan', 'selesai', 'dibatalkan')
            NOT NULL DEFAULT 'draft'
        ");
    }
};
