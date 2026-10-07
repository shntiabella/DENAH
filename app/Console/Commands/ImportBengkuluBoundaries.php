<?php

namespace App\Console\Commands;

use App\Models\AdministrativeArea;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportBengkuluBoundaries extends Command
{
    protected $signature = 'denah:import-boundaries
        {--path= : Direktori berisi file SQL batas administrasi (default: database/boundaries)}';

    protected $description = 'Import batas administrasi Provinsi Bengkulu (provinsi, kabupaten, kecamatan, desa) dari file SQL ke tabel administrative_areas.';

    public function handle(): int
    {
        $path = $this->option('path') ?? database_path('boundaries');
        if (! is_dir($path)) {
            $this->error("Direktori tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        DB::transaction(function () use ($path) {
            AdministrativeArea::query()->delete();
            $this->info('Tabel administrative_areas dikosongkan.');

            $province = $this->importProvince($path);
            $regencies = $this->importLevel($path, 'kab_wilayah_boundaries_kab_17.sql', AdministrativeArea::LEVEL_REGENCIES, $province);
            $districts = $this->importLevel($path, 'kec_wilayah_boundaries_kec_17.sql', AdministrativeArea::LEVEL_DISTRICTS, null, $regencies);
            $villages = $this->importVillages($path);

            $this->info(sprintf(
                'Selesai. Provinsi: %d, Kabupaten: %d, Kecamatan: %d, Desa/Kelurahan: %d',
                $province ? 1 : 0,
                count($regencies),
                count($districts),
                count($villages),
            ));
        });

        return self::SUCCESS;
    }

    protected function importProvince(string $path): ?AdministrativeArea
    {
        $files = glob($path.'/prov_wilayah_boundaries_prov_*.sql') ?: [];
        foreach ($files as $file) {
            foreach ($this->parseRows(file_get_contents($file)) as $row) {
                if ($row['code'] === '17') {
                    $this->info("Import provinsi: {$row['name']}");

                    return AdministrativeArea::create([
                        'code' => $row['code'],
                        'level' => AdministrativeArea::LEVEL_PROVINCE,
                        'name' => $row['name'],
                        'lat' => $row['lat'],
                        'lng' => $row['lng'],
                        'geojson' => $row['geojson'],
                    ]);
                }
            }
        }
        $this->error('Provinsi Bengkulu (kode 17) tidak ditemukan.');

        return null;
    }

    /** @return array<string, int> map kode -> id */
    protected function importLevel(string $path, string $file, string $level, ?AdministrativeArea $parent, array $regencyMap = []): array
    {
        $fullPath = $path.'/'.$file;
        if (! is_file($fullPath)) {
            $this->error("File tidak ditemukan: {$fullPath}");

            return [];
        }
        $map = [];
        foreach ($this->parseRows(file_get_contents($fullPath)) as $row) {
            $parentId = $parent?->id;
            if ($level === AdministrativeArea::LEVEL_DISTRICTS && $parent === null) {
                $parts = explode('.', $row['code']);
                $parentId = $regencyMap[implode('.', array_slice($parts, 0, 2))] ?? null;
            }
            $area = AdministrativeArea::create([
                'code' => $row['code'],
                'level' => $level,
                'name' => $row['name'],
                'parent_id' => $parentId,
                'lat' => $row['lat'],
                'lng' => $row['lng'],
                'geojson' => $row['geojson'],
            ]);
            $map[$row['code']] = $area->id;
            $this->line("  - [{$level}] {$row['name']} ({$row['code']})");
        }

        return $map;
    }

    /** @return array<string, int> map kode -> id */
    protected function importVillages(string $path): array
    {
        $districtMap = AdministrativeArea::where('level', AdministrativeArea::LEVEL_DISTRICTS)
            ->pluck('id', 'code')->all();

        $map = [];
        $files = glob($path.'/kel_17_wilayah_boundaries_kel_17.*.sql') ?: [];
        foreach ($files as $file) {
            $count = 0;
            foreach ($this->parseRows(file_get_contents($file)) as $row) {
                $code = $row['code']; // 17.xx.yy.zzzz
                $parts = explode('.', $code);
                $districtCode = implode('.', array_slice($parts, 0, 3));
                $parentId = $districtMap[$districtCode] ?? null;

                AdministrativeArea::create([
                    'code' => $code,
                    'level' => AdministrativeArea::LEVEL_VILLAGES,
                    'name' => $row['name'],
                    'parent_id' => $parentId,
                    'lat' => $row['lat'],
                    'lng' => $row['lng'],
                    'geojson' => $row['geojson'],
                ]);
                $map[$code] = true;
                $count++;
            }
            $this->line("  - Desa/Kelurahan {$count} baris dari ".basename($file));
        }

        return $map;
    }

    /**
     * Parse baris INSERT ... VALUES dari file SQL.
     * Format: ('kode','nama',lat,lng,'path')
     *
     * @return array<int, array{code: string, name: string, lat: float, lng: float, geojson: array}>
     */
    protected function parseRows(string $sql): array
    {
        preg_match('/INSERT INTO wilayah_boundaries\s*\((.*?)\)\s*VALUES\s*(.*);/s', $sql, $match);
        if (empty($match[2])) {
            return [];
        }

        $rows = [];
        $pattern = "/\(\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*(-?[\d.]+)\s*,\s*(-?[\d.]+)\s*,\s*'([^']*)'\s*\)/s";
        preg_match_all($pattern, $match[2], $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $path = str_replace("''", "'", $m[5]);
            $decoded = json_decode($path, true);
            $geojson = $this->toGeoJsonGeometry($decoded);

            if ($geojson === null) {
                $this->warn("  Abaikan wilayah tanpa polygon valid: {$m[2]}");
                continue;
            }

            $rows[] = [
                'code' => $m[1],
                'name' => trim($m[2]),
                'lat' => (float) $m[3],
                'lng' => (float) $m[4],
                'geojson' => $geojson,
            ];
        }

        return $rows;
    }

    /**
     * Konversi array path [lat, lon] ke geometri GeoJSON [lon, lat].
     * Struktur: `[ring1, ring2, ...]` (Polygon) atau `[[ring...], [ring...]]` (MultiPolygon).
     *
     * @return array|null
     */
    protected function toGeoJsonGeometry(mixed $decoded): ?array
    {
        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        $swap = static function (array $point): array {
            $lat = (float) ($point[0] ?? 0);
            $lon = (float) ($point[1] ?? 0);

            return [$lon, $lat];
        };

        $isNumeric = is_numeric($decoded[0][0][0] ?? null);

        if ($isNumeric) {
            // Polygon: array of rings
            $rings = [];
            foreach ($decoded as $ring) {
                if (! is_array($ring) || $ring === []) {
                    continue;
                }
                $rings[] = array_map($swap, $ring);
            }

            return ['type' => 'Polygon', 'coordinates' => $rings];
        }

        // MultiPolygon: array of polygons (each = array of rings)
        $polygons = [];
        foreach ($decoded as $polygon) {
            if (! is_array($polygon)) {
                continue;
            }
            $rings = [];
            foreach ($polygon as $ring) {
                if (! is_array($ring) || $ring === []) {
                    continue;
                }
                $rings[] = array_map($swap, $ring);
            }
            if ($rings !== []) {
                $polygons[] = $rings;
            }
        }

        if ($polygons === []) {
            return null;
        }

        return ['type' => 'MultiPolygon', 'coordinates' => $polygons];
    }
}
