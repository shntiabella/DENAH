<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=lahan_terbuka;charset=utf8mb4', 'root', '250806', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$keys = [
    'denah-cache-api_stats:v2:_:_:_:_',
    'denah-cache-api_stats:v2:_:Bengkulu Selatan:_:_',
    'denah-cache-api_stats:v2:_:Bengkulu Tengah:_:_',
    'denah-cache-api_stats:v2:2021:_:_:_',
    'denah-cache-api_stats:v2:2021:Bengkulu Selatan:_:_',
    'denah-cache-available_filters',
    'denah-cache-filter_years',
];
$stmt = $pdo->prepare("SELECT `value` FROM cache WHERE `key` = ?");
foreach ($keys as $k) {
    $stmt->execute([$k]);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if (!$row) { echo "--- KEY: $k : NOT FOUND\n"; continue; }
    $raw = $row[0];
    echo "--- KEY: $k (raw len=".strlen($raw).")\n";
    $val = @unserialize($raw);
    $mode = 'php-unserialized';
    if ($val === false) {
        $val = json_decode($raw, true);
        $mode = 'json-decoded';
    }
    echo "  mode: $mode\n";
    echo json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "\n";
}
