<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GeoJsonStreamReader;
use App\Support\GeoJsonAttributeMapper;

$reader = new GeoJsonStreamReader(__DIR__ . '/public/data/data_2025.geojson');
echo 'isFC: ' . var_export($reader->isFeatureCollection(), true) . PHP_EOL;
$count = 0;
$keys = [];
$sample = null;
foreach ($reader->features() as $feature) {
    $count++;
    foreach (array_keys($feature['properties'] ?? []) as $k) { $keys[$k] = 1; }
    if ($sample === null) { $sample = $feature; }
    if ($count > 100000) break;
}
echo 'features (capped 100k): ' . $count . PHP_EOL;
echo 'keys: ' . implode(', ', array_keys($keys)) . PHP_EOL;
echo 'sample props: ' . json_encode($sample['properties'] ?? null, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'regency: ' . var_export(GeoJsonAttributeMapper::regency($sample), true) . PHP_EOL;
echo 'district: ' . var_export(GeoJsonAttributeMapper::district($sample), true) . PHP_EOL;
echo 'village: ' . var_export(GeoJsonAttributeMapper::village($sample), true) . PHP_EOL;
echo 'areaHa: ' . GeoJsonAttributeMapper::areaHa($sample) . PHP_EOL;