<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'whereDate=' . App\Models\Jadwal::whereDate('tanggal', '2026-05-10')->count() . PHP_EOL;
echo 'whereBetween=' . App\Models\Jadwal::whereBetween('tanggal', ['2026-05-10', '2026-05-10'])->count() . PHP_EOL;
foreach (App\Models\Jadwal::whereDate('tanggal', '2026-05-10')->get(['id','tanggal','status','waktu_mulai','waktu_selesai']) as $item) {
    echo $item->id . '|' . $item->tanggal?->format('Y-m-d') . '|' . $item->status . '|' . optional($item->waktu_mulai)->format('H:i:s') . '|' . optional($item->waktu_selesai)->format('H:i:s') . PHP_EOL;
}
