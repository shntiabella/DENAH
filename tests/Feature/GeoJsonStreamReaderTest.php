<?php

use App\Support\GeoJsonStreamReader;

beforeEach(function () {
    $this->tempPaths = [];
});

afterEach(function () {
    foreach ($this->tempPaths ?? [] as $path) {
        @unlink($path);
    }
});

function writeTempGeojson(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'geojson_');
    file_put_contents($path, $contents);

    return $path;
}

it('yields each feature from a FeatureCollection file', function () {
    $path = writeTempGeojson(json_encode([
        'type' => 'FeatureCollection',
        'features' => [
            ['type' => 'Feature', 'properties' => ['name' => 'A']],
            ['type' => 'Feature', 'properties' => ['name' => 'B']],
        ],
    ]));
    $this->tempPaths[] = $path;

    $reader = new GeoJsonStreamReader($path);
    expect($reader->isFeatureCollection())->toBeTrue();

    $features = iterator_to_array($reader->features());
    expect($features)->toHaveCount(2)
        ->and($features[0]['properties']['name'])->toBe('A')
        ->and($features[1]['properties']['name'])->toBe('B');
});

it('ignores braces, quotes, and commas inside string values', function () {
    $note = 'area { "padat" , [semi] } dengan 1,5 km';
    $path = writeTempGeojson(json_encode([
        'type' => 'FeatureCollection',
        'features' => [
            ['type' => 'Feature', 'properties' => ['note' => $note]],
            ['type' => 'Feature', 'properties' => ['note' => 'kedua']],
        ],
    ]));
    $this->tempPaths[] = $path;

    $features = iterator_to_array((new GeoJsonStreamReader($path))->features());
    expect($features)->toHaveCount(2)
        ->and($features[0]['properties']['note'])->toBe($note)
        ->and($features[1]['properties']['note'])->toBe('kedua');
});

it('streams files larger than one read chunk', function () {
    $features = [];
    for ($i = 0; $i < 40_000; $i++) {
        $features[] = ['type' => 'Feature', 'properties' => ['name' => 'F'.$i, 'blob' => str_repeat('x', 40)]];
    }
    $json = json_encode(['type' => 'FeatureCollection', 'features' => $features]);
    expect(strlen($json))->toBeGreaterThan(1_048_576);

    $path = writeTempGeojson($json);
    $this->tempPaths[] = $path;

    $features = iterator_to_array((new GeoJsonStreamReader($path))->features());
    expect($features)->toHaveCount(40_000)
        ->and($features[39_999]['properties']['name'])->toBe('F39999');
});

it('detects a single Feature as not a collection', function () {
    $path = writeTempGeojson(json_encode([
        'type' => 'Feature',
        'geometry' => ['type' => 'Point', 'coordinates' => [112.5, -7.0]],
        'properties' => ['name' => 'Tunggal'],
    ]));
    $this->tempPaths[] = $path;

    $reader = new GeoJsonStreamReader($path);
    expect($reader->isFeatureCollection())->toBeFalse();
});

it('returns false for non-collection documents', function () {
    $path = writeTempGeojson(json_encode(['type' => 'GeometryCollection', 'geometries' => []]));
    $this->tempPaths[] = $path;

    expect((new GeoJsonStreamReader($path))->isFeatureCollection())->toBeFalse();
});

it('throws when iterating features of a non-collection document', function () {
    $path = writeTempGeojson(json_encode(['type' => 'Feature', 'properties' => []]));
    $this->tempPaths[] = $path;

    iterator_to_array((new GeoJsonStreamReader($path))->features());
})->throws(RuntimeException::class);
