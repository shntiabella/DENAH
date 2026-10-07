<?php

namespace Database\Seeders;

use App\Support\GeoJsonStreamReader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Ekstrak metadata wilayah (kabupaten, kecamatan, desa) dari file GeoJSON
 * ke tabel `administrative_areas` dan agregat statistik ke `stat_permukiman`.
 *
 * Berbeda dengan `denah:import-boundaries` yang mengimpor polygon batas
 * dari file SQL, seeder ini mengekstrak atribut wilayah langsung dari
 * property feature GeoJSON raster (WADMKK, WADMKC, NAMOBJ) sehingga
 * metadata filter tidak pernah null setelah server restart.
 *
 * Jalankan via:
 *   php artisan db:seed --class=GeoJsonMetaDataSeeder
 *   php artisan denah:seed-metadata
 */
class GeoJsonMetaDataSeeder extends Seeder
{
    /**
     * Peta nama kolom GeoJSON → nama standar DENAH.
     */
    private const PROP_MAP = [
        'WADMKK' => 'regency',
        'WADMKC' => 'district',
        'NAMOBJ' => 'village',
    ];

    public function run(): void
    {
        $files = $this->discoverGeoJsonFiles();
        if ($files === []) {
            $this->command?->warn('Tidak ada file data_YYYY.geojson ditemukan di public/data/');

            return;
        }

        $this->command?->info('Memindai '.count($files).' file GeoJSON untuk metadata wilayah...');

        $metadata = $this->extractMetadata($files);
        $this->persistMetadata($metadata);
        $this->aggregateStatistics($metadata);

        Cache::forget('home_stats');
        Cache::forget('stat_permukiman');
        Cache::forget('filter_years');
        Cache::forget('filter_regencies');

        $this->command?->info('Metadata wilayah dan statistik berhasil disimpan ke database.');
    }

    /** @return array<int, string> */
    protected function discoverGeoJsonFiles(): array
    {
        $dir = public_path('data');
        $files = [];

        foreach (glob($dir.'/data_*.geojson') ?: [] as $path) {
            if (preg_match('/data_(\d{4})\.geojson$/', $path)) {
                $files[] = $path;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Scan semua file GeoJSON dan kumpulkan metadata unik wilayah + statistik per wilayah x tahun.
     *
     * @return array{regions: array<string, array{name: string, level: string, parent: string|null}>, stats: array<string, array{year: int, regency: string|null, district: string|null, village: string|null, luas_ha: float, jumlah_lokasi: int, jumlah_records: int}>, years: int[]}
     */
    protected function extractMetadata(array $files): array
    {
        $regions = [];
        $stats = [];

        foreach ($files as $file) {
            if (! preg_match('/data_(\d{4})\.geojson$/', $file, $ym)) {
                continue;
            }
            $year = (int) $ym[1];

            $reader = new GeoJsonStreamReader($file);
            if (! $reader->isFeatureCollection()) {
                continue;
            }

            foreach ($reader->features() as $feature) {
                if (! is_array($feature)) {
                    continue;
                }
                $props = $feature['properties'] ?? [];

                $regency = $this->extractProp($props, 'regency');
                $district = $this->extractProp($props, 'district');
                $village = $this->extractProp($props, 'village');

                $areaHa = $this->computeAreaHa($feature);

                // Simpan region unik
                if ($regency) {
                    $key = 'kabupaten:'.mb_strtolower($regency);
                    $regions[$key] ??= ['name' => $regency, 'level' => 'kabupaten', 'parent' => null];
                }
                if ($district) {
                    $key = 'kecamatan:'.mb_strtolower($district);
                    $regions[$key] ??= ['name' => $district, 'level' => 'kecamatan', 'parent' => $regency];
                }
                if ($village) {
                    $key = 'desa:'.mb_strtolower($village).':'.mb_strtolower($district ?? '');
                    $regions[$key] ??= ['name' => $village, 'level' => 'desa', 'parent' => $district];
                }

                // Agregat statistik per tahun x wilayah
                $statKey = "{$year}:{$regency}:{$district}:{$village}";
                if (! isset($stats[$statKey])) {
                    $stats[$statKey] = [
                        'year' => $year,
                        'regency' => $regency,
                        'district' => $district,
                        'village' => $village,
                        'luas_ha' => 0,
                        'jumlah_lokasi' => 0,
                        'jumlah_records' => 0,
                    ];
                }
                $stats[$statKey]['luas_ha'] += $areaHa;
                $stats[$statKey]['jumlah_lokasi'] += 1;
                $stats[$statKey]['jumlah_records'] += 1;
            }

            $this->command?->line("  Scan: {$file}");
        }

        return ['regions' => $regions, 'stats' => $stats, 'years' => array_unique(array_map(function ($s) { return $s['year']; }, $stats))];
    }

    protected function extractProp(array $props, string $standard): ?string
    {
        foreach (self::PROP_MAP as $geoKey => $std) {
            if ($std === $standard && ! empty($props[$geoKey])) {
                $val = trim((string) $props[$geoKey]);

                return $val !== '' ? $val : null;
            }
        }

        return null;
    }

    /**
     * Hitung luas Ha dari geometri GeoJSON menggunakan rumus shoelace.
     */
    protected function computeAreaHa(array $feature): float
    {
        $geom = $feature['geometry'] ?? null;
        if (! $geom || empty($geom['coordinates'])) {
            return 0;
        }

        $coords = $geom['coordinates'];
        $type = $geom['type'] ?? '';

        if ($type === 'Polygon') {
            return $this->ringAreaHa($coords) * 100;
        }
        if ($type === 'MultiPolygon') {
            $total = 0;
            foreach ($coords as $polygon) {
                $total += $this->ringAreaHa($polygon) * 100;
            }

            return $total;
        }

        return 0;
    }

    protected function ringAreaHa(array $rings): float
    {
        if (empty($rings) || empty($rings[0])) {
            return 0;
        }
        $ring = $rings[0];
        $n = count($ring);
        if ($n < 4) {
            return 0;
        }

        $sumLat = 0;
        for ($i = 0; $i < $n - 1; $i++) {
            $sumLat += $ring[$i][1];
        }
        $cos0 = cos(($sumLat / ($n - 1)) * M_PI / 180);
        $R = 6378137;
        $total = 0;
        for ($i = 0; $i < $n - 1; $i++) {
            $x1 = $R * $cos0 * $ring[$i][0] * M_PI / 180;
            $y1 = $R * $ring[$i][1] * M_PI / 180;
            $x2 = $R * $cos0 * $ring[$i + 1][0] * M_PI / 180;
            $y2 = $R * $ring[$i + 1][1] * M_PI / 180;
            $total += ($x1 * $y2 - $x2 * $y1);
        }

        return abs($total) / 2 / 1e6;
    }

    /**
     * Simpan metadata wilayah ke `administrative_areas` (hanya jika belum ada).
     */
    protected function persistMetadata(array $metadata): void
    {
        $existing = DB::table('administrative_areas')->pluck('name', 'level')->toArray();

        $inserted = 0;
        foreach ($metadata['regions'] as $region) {
            $name = $region['name'];
            $level = $region['level'];
            $parentName = $region['parent'];

            // Cek duplikat berdasarkan level + name
            $exists = false;
            foreach ($existing as $lvl => $names) {
                if (is_array($names)) {
                    foreach ($names as $n => $id) {
                        if ($lvl === $level && $n === $name) {
                            $exists = true;
                            break 2;
                        }
                    }
                }
            }

            if ($exists) {
                continue;
            }

            DB::table('administrative_areas')->insert([
                'level' => $level,
                'name' => $name,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inserted++;
        }

        $this->command?->info("  {$inserted} wilayah baru ditambahkan ke administrative_areas.");
    }

    /**
     * Simpan statistik agregat ke `stat_permukiman`.
     */
    protected function aggregateStatistics(array $metadata): void
    {
        $rows = [];
        foreach ($metadata['stats'] as $stat) {
            $rows[] = [
                'year' => $stat['year'],
                'regency' => $stat['regency'],
                'district' => $stat['district'],
                'village' => $stat['village'],
                'luas_ha' => round($stat['luas_ha'], 4),
                'jumlah_lokasi' => $stat['jumlah_lokasi'],
                'jumlah_records' => $stat['jumlah_records'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Chunk insert untuk memori efisien
        $chunks = array_chunk($rows, 500);
        $total = 0;
        foreach ($chunks as $chunk) {
            DB::table('stat_permukiman')->insert($chunk);
            $total += count($chunk);
        }

        $this->command?->info("  {$total} baris statistik disimpan ke stat_permukiman.");
    }
}
