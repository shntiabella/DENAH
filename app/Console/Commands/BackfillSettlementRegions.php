<?php

namespace App\Console\Commands;

use App\Models\SettlementArea;
use App\Support\GeoPointInPolygon;
use Illuminate\Console\Command;

class BackfillSettlementRegions extends Command
{
    protected $signature = 'denah:backfill-settlement-regions {--chunk=1000 : Jumlah baris per batch} {--grid=0.05 : Grid size derajat}';

    protected $description = 'Isi regency/district/village yang NULL dari batas_wilayah.json (point-in-polygon + grid index).';

    /** @var array<int, array<int, list<int>>> Grid index: grid[row][col] = list of feature indices */
    protected array $grid = [];

    protected float $minLon = 0;
    protected float $minLat = 0;
    protected float $gridSize = 0.05;

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $this->gridSize = (float) $this->option('grid');
        if ($this->gridSize <= 0) {
            $this->gridSize = 0.05;
        }

        $this->info('Memuat batas wilayah dari batas_wilayah.json …');
        [$features, $bbox] = $this->loadFeatures();
        $this->minLon = $bbox[0];
        $this->minLat = $bbox[1];
        $this->buildGrid($features);

        $this->line(sprintf('  %d fitur wilayah, grid bounds: lon [%.4f–%.4f] lat [%.4f–%.4f]',
            count($features), $bbox[0], $bbox[2], $bbox[1], $bbox[3]));

        $chunk = (int) $this->option('chunk');
        $updated = 0;
        $skipped = 0;
        $total = 0;

        $total = SettlementArea::where(function ($q) {
            $q->whereNull('village')->orWhere('village', '');
        })->count();
        $this->info("Membackfill {$total} baris permukiman …");

        $rows = [];
        SettlementArea::where(function ($q) {
            $q->whereNull('village')->orWhere('village', '');
        })
            ->select('id', 'geojson_data')
            ->chunkById($chunk, function ($areas) use (&$rows, &$updated, &$skipped, $features) {
                foreach ($areas as $area) {
                    $geometry = $this->geometryOf($area->geojson_data);
                    $point = $geometry !== null
                        ? GeoPointInPolygon::representativePoint($geometry)
                        : null;

                    if ($point === null) {
                        $skipped++;
                        continue;
                    }

                    [$lon, $lat] = $point;

                    // Find candidate features via grid index
                    $candidateIndices = $this->getCandidates($lon, $lat);

                    $village = null;
                    $district = null;
                    $regency = null;

                    foreach ($candidateIndices as $idx) {
                        $f = $features[$idx];
                        if (GeoPointInPolygon::contains($f['geometry'], $lon, $lat)) {
                            $regency = $f['regency'];
                            $district = $f['district'];
                            $village = $f['village'];
                            break;
                        }
                    }

                    if ($village === null && $district === null && $regency === null) {
                        $skipped++;
                        continue;
                    }

                    $rows[] = [
                        'id' => $area->id,
                        'regency' => $regency,
                        'district' => $district,
                        'village' => $village,
                    ];
                    $updated++;

                    if (count($rows) >= 5000) {
                        $this->bulkUpdate($rows);
                        $rows = [];
                        $this->output->write('.');
                    }
                }

                if ($rows !== []) {
                    $this->bulkUpdate($rows);
                    $rows = [];
                    $this->output->write('.');
                }
            });

        $this->line('');
        $this->info("Selesai. {$updated} baris ter-update, {$skipped} dilewati.");

        return self::SUCCESS;
    }

    protected function loadFeatures(): array
    {
        $path = public_path('data/batas_wilayah.json');
        if (! file_exists($path)) {
            $this->error("File {$path} tidak ditemukan!");
            return [[], [0, 0, 0, 0]];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        $rawFeatures = $data['features'] ?? [];
        unset($json, $data);

        $features = [];
        $minLon = INF;
        $minLat = INF;
        $maxLon = -INF;
        $maxLat = -INF;

        foreach ($rawFeatures as $feature) {
            $props = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;
            if ($geometry === null || $geometry === []) {
                continue;
            }

            $regName = $props['LEVEL_4'] ?? null;
            $disName = $props['LEVEL_5'] ?? null;
            $vilName = $props['LEVEL_6'] ?? null;
            if ($regName === null || $disName === null || $vilName === null) {
                continue;
            }

            $bbox = $this->geometryBBox($geometry);
            if (! is_finite($bbox[0]) || ! is_finite($bbox[2])) {
                continue;
            }
            $minLon = min($minLon, $bbox[0]);
            $minLat = min($minLat, $bbox[1]);
            $maxLon = max($maxLon, $bbox[2]);
            $maxLat = max($maxLat, $bbox[3]);

            $features[] = [
                'geometry' => $geometry,
                'regency' => $regName,
                'district' => $disName,
                'village' => $vilName,
                'bbox' => $bbox,
            ];
        }

        unset($rawFeatures);

        return [$features, [$minLon, $minLat, $maxLon, $maxLat]];
    }

    protected function buildGrid(array $features): void
    {
        $this->grid = [];
        foreach ($features as $idx => $f) {
            [$loLon, $loLat, $hiLon, $hiLat] = $f['bbox'];
            if (! is_finite($loLon) || ! is_finite($hiLon)) {
                continue;
            }
            $col0 = (int) floor(($loLon - $this->minLon) / $this->gridSize);
            $row0 = (int) floor(($loLat - $this->minLat) / $this->gridSize);
            $col1 = (int) floor(($hiLon - $this->minLon) / $this->gridSize);
            $row1 = (int) floor(($hiLat - $this->minLat) / $this->gridSize);

            for ($r = $row0; $r <= $row1; $r++) {
                for ($c = $col0; $c <= $col1; $c++) {
                    $this->grid[$r][$c][] = $idx;
                }
            }
        }
    }

    /** @return list<int> */
    protected function getCandidates(float $lon, float $lat): array
    {
        $col = (int) floor(($lon - $this->minLon) / $this->gridSize);
        $row = (int) floor(($lat - $this->minLat) / $this->gridSize);

        $result = [];
        for ($r = $row - 1; $r <= $row + 1; $r++) {
            for ($c = $col - 1; $c <= $col + 1; $c++) {
                if (isset($this->grid[$r][$c])) {
                    $result = array_merge($result, $this->grid[$r][$c]);
                }
            }
        }

        return $result;
    }

    /** @return array{0: float, 1: float, 2: float, 3: float} */
    protected function geometryBBox(array $geometry): array
    {
        $minLon = INF;
        $minLat = INF;
        $maxLon = -INF;
        $maxLat = -INF;

        $walk = function (array $coords) use (&$walk, &$minLon, &$minLat, &$maxLon, &$maxLat) {
            foreach ($coords as $point) {
                if (! is_array($point) || count($point) < 2) {
                    continue;
                }
                if (is_array($point[0])) {
                    $walk($point);
                } else {
                    $lon = (float) $point[0];
                    $lat = (float) $point[1];
                    $minLon = min($minLon, $lon);
                    $minLat = min($minLat, $lat);
                    $maxLon = max($maxLon, $lon);
                    $maxLat = max($maxLat, $lat);
                }
            }
        };

        if (isset($geometry['coordinates'])) {
            $walk($geometry['coordinates']);
        }

        return [$minLon, $minLat, $maxLon, $maxLat];
    }

    protected function bulkUpdate(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $updates = [];
        foreach ($rows as $r) {
            $updates[(int) $r['id']] = [
                'regency' => $r['regency'],
                'district' => $r['district'],
                'village' => $r['village'],
            ];
        }

        foreach (array_chunk($updates, 2000, true) as $chunkRows) {
            $ids = array_keys($chunkRows);

            $case = function (string $column) use ($chunkRows): string {
                $sql = 'CASE id';
                foreach ($chunkRows as $id => $v) {
                    if ($v[$column] === null) {
                        $sql .= " WHEN {$id} THEN NULL";
                        continue;
                    }
                    $escaped = \Illuminate\Support\Facades\DB::getPdo()->quote((string) $v[$column]);
                    $sql .= " WHEN {$id} THEN {$escaped}";
                }

                return $sql . ' END';
            };

            \Illuminate\Support\Facades\DB::table('settlement_areas')
                ->whereIn('id', $ids)
                ->update([
                    'regency' => \Illuminate\Support\Facades\DB::raw($case('regency')),
                    'district' => \Illuminate\Support\Facades\DB::raw($case('district')),
                    'village' => \Illuminate\Support\Facades\DB::raw($case('village')),
                ]);
        }
    }

    protected function geometryOf(mixed $geojson): ?array
    {
        if (is_string($geojson)) {
            $geojson = json_decode($geojson, true);
        }

        if (! is_array($geojson)) {
            return null;
        }

        $type = $geojson['type'] ?? null;
        $geom = $geojson['geometry'] ?? null;

        if ($type === 'Feature') {
            return is_array($geom) ? $geom : null;
        }

        if ($type === 'FeatureCollection') {
            foreach ($geojson['features'] ?? [] as $feature) {
                if (is_array($feature) && is_array($feature['geometry'] ?? null)) {
                    return $feature['geometry'];
                }
            }

            return null;
        }

        return is_array($geom) ? $geom : null;
    }
}
