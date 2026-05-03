<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app(App\Http\Controllers\Pj\JadwalKegiatanController::class);
$ref = new ReflectionClass($controller);
$method = $ref->getMethod('buildAgendaItems');
$method->setAccessible(true);
$result = $method->invoke($controller, Carbon\Carbon::parse('2026-05-10')->startOfDay(), Carbon\Carbon::parse('2026-05-10')->endOfDay(), now());
echo 'items=' . $result->count() . PHP_EOL;
foreach ($result as $item) {
    echo $item['key'] . '|' . $item['type'] . '|' . $item['title'] . '|' . $item['meta_status'] . '|' . $item['phase'] . PHP_EOL;
}
