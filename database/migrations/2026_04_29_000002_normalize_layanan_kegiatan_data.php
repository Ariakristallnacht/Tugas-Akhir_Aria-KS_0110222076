<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameOrEnsure(
            oldName: 'PIPP & Skrining Kesehatan BPJS',
            newName: 'Skrining Kesehatan BPJS / CKG',
            deskripsi: 'Layanan skrining kesehatan BPJS dan CKG.'
        );

        $this->renameOrEnsure(
            oldName: 'Layanan Lansia (>60 tahun)',
            newName: 'Klaster 3 Layanan Lansia',
            deskripsi: 'Poli layanan kesehatan lansia.'
        );

        $this->renameOrEnsure(
            oldName: 'Apotek',
            newName: 'Klaster 5 Apotek',
            deskripsi: 'Layanan farmasi dan pengambilan obat.'
        );

        $this->ensureExists(
            name: 'PIPP',
            deskripsi: 'Layanan PIPP Puskesmas Bunar.'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perubahan data ini tidak dibalik otomatis agar tidak merusak data jadwal yang sudah terhubung.
    }

    private function renameOrEnsure(string $oldName, string $newName, string $deskripsi): void
    {
        $existingNew = DB::table('kegiatan')->where('nama_kegiatan', $newName)->first();
        $existingOld = DB::table('kegiatan')->where('nama_kegiatan', $oldName)->first();

        if ($existingOld && ! $existingNew) {
            DB::table('kegiatan')
                ->where('id', $existingOld->id)
                ->update([
                    'nama_kegiatan' => $newName,
                    'jenis' => 'layanan',
                    'deskripsi' => $deskripsi,
                    'is_aktif' => true,
                    'updated_at' => now(),
                ]);

            return;
        }

        if (! $existingNew) {
            $this->ensureExists($newName, $deskripsi);
        }

        if ($existingOld) {
            DB::table('kegiatan')
                ->where('id', $existingOld->id)
                ->update([
                    'is_aktif' => false,
                    'updated_at' => now(),
                ]);
        }
    }

    private function ensureExists(string $name, string $deskripsi): void
    {
        $exists = DB::table('kegiatan')->where('nama_kegiatan', $name)->exists();

        if ($exists) {
            DB::table('kegiatan')
                ->where('nama_kegiatan', $name)
                ->update([
                    'jenis' => 'layanan',
                    'deskripsi' => $deskripsi,
                    'is_aktif' => true,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('kegiatan')->insert([
            'nama_kegiatan' => $name,
            'jenis' => 'layanan',
            'deskripsi' => $deskripsi,
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
