<?php
foreach ([2021,2022,2023,2024,2025] as $y) {
    $path = "public/data/data_{$y}.geojson";
    $data = json_decode(file_get_contents($path), true);
    $features = $data['features'] ?? [];
    $keys = [];
    $classifica = [];
    $countSum = 0; $countMax = 0;
    $geomTypes = [];
    foreach ($features as $f) {
        foreach (array_keys($f['properties'] ?? []) as $k) { $keys[$k] = true; }
        $c = $f['properties']['classifica'] ?? null; $classifica[$c] = ($classifica[$c] ?? 0) + 1;
        $cnt = $f['properties']['count'] ?? 0; $countSum += $cnt; $countMax = max($countMax, $cnt);
        $g = $f['geometry']['type'] ?? '?'; $geomTypes[$g] = true;
    }
    echo "== $y: features=".count($features)." keys=".implode(',', array_keys($keys))." geom=".implode(',', array_keys($geomTypes))." countSum=$countSum countMax=$countMax classifica=".json_encode($classifica).PHP_EOL;
}