<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeArea;
use App\Models\SettlementArea;
use App\Support\GeoJsonAttributeMapper;
use App\Support\GeoJsonStreamReader;
use App\Support\SettlementStatistics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SettlementStatsController extends Controller
{
    private const CACHE_TTL = 86400;

    /**
     * GET /api/settlement-stats?year=&regency=&district=&village=
     * Statistik agregat permukiman dengan filter wilayah & tahun.
     * Hasil di-cache 24 jam per kombinasi filter.
     */
    public function index(Request $request): JsonResponse
    {
        $year = $request->filled('year') ? $request->integer('year') : null;
        $regency = $request->filled('regency') ? (string) $request->input('regency') : null;
        $district = $request->filled('district') ? (string) $request->input('district') : null;
        $village = $request->filled('village') ? (string) $request->input('village') : null;

        $cacheKey = 'api_stats:v2:'.implode(':', array_map(fn ($v) => $v ?? '_', [$year, $regency, $district, $village]));

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year, $regency, $district, $village) {
            $query = SettlementStatistics::query($year, $regency, $district, $village);

            return [
                'totals' => SettlementStatistics::totals(clone $query),
                'by_year' => SettlementStatistics::byYear(clone $query),
                'by_regency' => SettlementStatistics::byRegency(clone $query),
                'ranking_districts' => SettlementStatistics::ranking(clone $query, 'district'),
                'ranking_villages' => SettlementStatistics::ranking(clone $query, 'village'),
            ];
        });

        return response()->json([
            'filters' => [
                'year' => $year,
                'regency' => $regency,
                'district' => $district,
                'village' => $village,
            ],
            'available' => $this->availableFilters(),
            'totals' => $data['totals'],
            'by_year' => $data['by_year'],
            'by_regency' => $data['by_regency'],
            'ranking_districts' => $data['ranking_districts'],
            'ranking_villages' => $data['ranking_villages'],
        ]);
    }

    /**
     * GET /api/settlement-geojson-chunk?year=2024&regency=Bengkulu+Selatan
     * GeoJSON raster yang sudah di-filter per kabupaten agar client tidak
     * perlu memuat file raksasa sekaligus.
     */
    public function geojsonChunk(Request $request): JsonResponse
    {
        $year = $request->filled('year') ? $request->integer('year') : null;
        $regency = $request->filled('regency') ? (string) $request->input('regency') : null;
        $district = $request->filled('district') ? (string) $request->input('district') : null;
        $village = $request->filled('village') ? (string) $request->input('village') : null;

        if ($year === null) {
            return response()->json(['error' => 'Parameter year wajib diisi.'], 422);
        }

        $file = public_path("data/data_{$year}.geojson");
        if (! is_file($file)) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }

        $cacheKey = "geojson_chunk:{$year}:".implode(':', array_map(fn ($v) => $v ?? '_', [$regency, $district, $village]));
        $cacheTTL = self::CACHE_TTL;

        $collection = Cache::remember($cacheKey, $cacheTTL, function () use ($file, $regency, $district, $village) {
            $reader = new GeoJsonStreamReader($file);
            if (! $reader->isFeatureCollection()) {
                return ['type' => 'FeatureCollection', 'features' => []];
            }

            $features = [];
            foreach ($reader->features() as $feature) {
                if (! is_array($feature)) {
                    continue;
                }
                $props = $feature['properties'] ?? [];

                if ($regency && $this->getProp($props, 'regency') !== $regency) {
                    continue;
                }
                if ($district && $this->getProp($props, 'district') !== $district) {
                    continue;
                }
                if ($village && $this->getProp($props, 'village') !== $village) {
                    continue;
                }

                $features[] = $feature;
            }

            return ['type' => 'FeatureCollection', 'features' => $features];
        });

        return response()->json($collection)->header('Cache-Control', 'public, max-age=86400');
    }

    /** @return array<string, mixed> */
    protected function availableFilters(): array
    {
        return Cache::remember('available_filters', self::CACHE_TTL, function () {
            return [
                'years' => SettlementArea::where('status', 'published')
                    ->distinct()->orderByDesc('year')->pluck('year')->values()->all(),
                'regencies' => SettlementArea::where('status', 'published')
                    ->whereNotNull('regency')
                    ->distinct()->orderBy('regency')->pluck('regency')->values()->all(),
                'districts' => SettlementArea::where('status', 'published')
                    ->whereNotNull('district')
                    ->distinct()->orderBy('district')->pluck('district')->values()->all(),
                'villages' => SettlementArea::where('status', 'published')
                    ->whereNotNull('village')
                    ->distinct()->orderBy('village')->pluck('village')->values()->all(),
            ];
        });
    }

    /**
     * Ekstrak nama wilayah dari properti GeoJSON (standarisasi nama kolom).
     */
    private function getProp(array $props, string $key): ?string
    {
        $map = [
            'regency' => ['WADMKK', 'regency', 'KABUPATEN', 'Kabupaten'],
            'district' => ['WADMKC', 'district', 'KECAMATAN', 'Kecamatan'],
            'village' => ['NAMOBJ', 'village', 'DESA', 'Desa', 'KELURAHAN', 'Kelurahan'],
        ];

        foreach ($map[$key] ?? [] as $k) {
            if (! empty($props[$k])) {
                $val = trim((string) $props[$k]);

                return $val !== '' ? $val : null;
            }
        }

        return null;
    }
}
