<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'jadwal=' . App\Models\Jadwal::whereDate('tanggal', '2026-05-10')->count() . PHP_EOL;
echo 'dinas=' . App\Models\PengajuanDinas::whereDate('tanggal_mulai', '<=', '2026-05-10')->whereDate('tanggal_selesai', '>=', '2026-05-10')->count() . PHP_EOL;

$controller = app(App\Http\Controllers\Pj\JadwalKegiatanController::class);
$ref = new ReflectionClass($controller);
$method = $ref->getMethod('buildAgendaItems');
$method->setAccessible(true);
$result = $method->invoke($controller, Carbon\Carbon::parse('2026-05-10')->startOfDay(), Carbon\Carbon::parse('2026-05-10')->endOfDay(), now());

echo 'items=' . $result->count() . PHP_EOL;
foreach ($result as $item) {
    echo $item['key'] . '|' . $item['type'] . '|' . $item['title'] . '|' . $item['meta_status'] . '|' . $item['phase'] . PHP_EOL;
}
