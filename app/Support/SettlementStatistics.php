<?php

namespace App\Support;

use App\Models\AdministrativeArea;
use App\Models\SettlementArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Sumber kebenaran tunggal (single source of truth) untuk seluruh agregasi
 * statistik permukiman DENAH. Dipakai bersama oleh halaman Statistik publik,
 * panel statistik peta interaktif, dashboard admin, dan halaman Statistik admin.
 *
 * Seluruh angka dihitung langsung dari tabel settlement_areas (database).
 * Agregasi memakai SQL GROUP BY / aggregate (bukan memuat seluruh baris ke
 * memori) agar tetap ringan dengan ratusan ribu record beserta blob GeoJSON.
 *
 * Hasil query di-cache selama 24 jam (86400 detik) per kombinasi filter agar
 * server tidak perlu menghitung ulang SUM/COUNT setiap kali halaman di-refresh.
 */
final class SettlementStatistics
{
    private const CACHE_TTL = 86400; // 24 jam

    /**
     * Kolom wilayah yang diizinkan untuk filter (whitelist anti SQL injection).
     */
    private const REGION_COLUMNS = ['province', 'regency', 'district', 'village'];

    /**
     * Query dasar permukiman terpublikasi dengan filter tahun & wilayah.
     */
    public static function query(
        ?int $year = null,
        ?string $regency = null,
        ?string $district = null,
        ?string $village = null,
    ): Builder {
        $q = SettlementArea::query()
            ->where('status', 'published')
            ->when($year !== null, fn (Builder $q) => $q->where('year', $year));

        foreach (['regency' => $regency, 'district' => $district, 'village' => $village] as $column => $value) {
            $q = self::applyRegionFilter($q, $column, $value);
        }

        return $q;
    }

    /**
     * Filter wilayah dengan pencocokan yang toleran terhadap beda huruf
     * besar/kecil dan spasi (UPPER/TRIM) antara nilai dropdown
     * (AdministrativeArea) dengan kolom di tabel settlement_areas.
     */
    private static function applyRegionFilter(Builder $q, string $column, ?string $value): Builder
    {
        if ($value === null || trim($value) === '') {
            return $q;
        }

        if (! in_array($column, self::REGION_COLUMNS, true)) {
            return $q;
        }

        $normalized = Str::upper(Str::squish(str_replace("\u{A0}", ' ', trim((string) $value))));

        return $q->whereRaw('UPPER(TRIM('.$column.')) = ?', [$normalized]);
    }

    /** @return Collection<int, int> */
    public static function availableYears(): Collection
    {
        $data = Cache::remember('filter_years', self::CACHE_TTL, function () {
            return SettlementArea::where('status', 'published')
                ->distinct()
                ->orderBy('year')
                ->pluck('year')
                ->toArray();
        });

        return collect($data);
    }

    /** @return Collection<int, string> */
    public static function availableRegencies(): Collection
    {
        $data = Cache::remember('filter_regencies', self::CACHE_TTL, function () {
            return SettlementArea::where('status', 'published')
                ->whereNotNull('regency')
                ->distinct()
                ->orderBy('regency')
                ->pluck('regency')
                ->toArray();
        });

        return collect($data);
    }

    /** @return Collection<int, string> */
    public static function availableDistricts(?string $regency = null): Collection
    {
        $key = $regency
            ? "filter_districts:{$regency}"
            : 'filter_districts';

        $data = Cache::remember($key, self::CACHE_TTL, function () use ($regency) {
            return SettlementArea::where('status', 'published')
                ->whereNotNull('district')
                ->when($regency !== null && trim($regency) !== '', fn (Builder $q) => self::applyRegionFilter($q, 'regency', $regency))
                ->distinct()
                ->orderBy('district')
                ->pluck('district')
                ->toArray();
        });

        return collect($data);
    }

    /** @return Collection<int, string> */
    public static function availableVillages(?string $district = null): Collection
    {
        $key = $district
            ? "filter_villages:{$district}"
            : 'filter_villages';

        $data = Cache::remember($key, self::CACHE_TTL, function () use ($district) {
            return SettlementArea::where('status', 'published')
                ->whereNotNull('village')
                ->when($district !== null && trim($district) !== '', fn (Builder $q) => self::applyRegionFilter($q, 'district', $district))
                ->distinct()
                ->orderBy('village')
                ->pluck('village')
                ->toArray();
        });

        return collect($data);
    }

    /**
     * Generate cache key dari parameter filter.
     */
    private static function cacheKey(string $prefix, ?int $year, ?string $regency, ?string $district, ?string $village): string
    {
        // "v2" membatalkan cache lama yang dihitung dengan pencocokan wilayah
        // eksak (sebelum UPPER/TRIM), sehingga hasil baru langsung terlihat.
        return $prefix.':v2:'.implode(':', array_map(fn ($v) => $v ?? '_', [$year, $regency, $district, $village]));
    }

    /**
     * Ringkasan lengkap untuk satu kumpulan filter (halaman Statistik/admin).
     *
     * @return array<string, mixed>
     */
    public static function overview(?int $year = null, ?string $regency = null, ?string $district = null, ?string $village = null): array
    {
        $key = self::cacheKey('stats_overview', $year, $regency, $district, $village);

        return Cache::remember($key, self::CACHE_TTL, function () use ($year, $regency, $district, $village) {
            $baseQuery = self::query(null, $regency, $district, $village);
            $scoped = (clone $baseQuery)
                ->when($year !== null, fn (Builder $q) => $q->where('year', $year));

            return [
                'totals' => self::totals(clone $scoped),
                'by_year' => self::byYear(clone $scoped),
                'by_regency' => self::byRegency(clone $scoped),
                'ranking_districts' => self::ranking(clone $scoped, 'district'),
                'ranking_villages' => self::ranking(clone $scoped, 'village'),
                'change' => self::yearChange(clone $baseQuery, $year),
            ];
        });
    }

    /**
     * Statistik untuk satu wilayah administrasi (panel kanan peta).
     *
     * @return array<string, mixed>
     */
    public static function areaOverview(AdministrativeArea $area, ?int $year = null): array
    {
        $key = self::cacheKey('stats_area', $year, null, null, null).':'.$area->id;

        return Cache::remember($key, self::CACHE_TTL, function () use ($area, $year) {
            $baseQuery = SettlementArea::query()
                ->where('status', 'published')
                ->forAdministrativeArea($area);

            $scoped = (clone $baseQuery)
                ->when($year !== null, fn (Builder $q) => $q->where('year', $year));

            return [
                'area' => [
                    'id' => $area->id,
                    'code' => $area->code,
                    'name' => $area->name,
                    'level' => $area->level,
                    'levelLabel' => $area->levelLabel(),
                    'fullName' => $area->fullName(),
                    'lat' => $area->lat,
                    'lng' => $area->lng,
                ],
                'totals' => self::totals(clone $scoped),
                'by_year' => self::byYear(clone $scoped),
                'change' => self::yearChange(clone $baseQuery, $year),
            ];
        });
    }

    /**
     * Ringkasan agregat untuk satu query (sudah berisi filter yang diinginkan).
     *
     * @return array<string, int|float>
     */
    public static function totals(Builder $query): array
    {
        $row = (clone $query)
            ->selectRaw('coalesce(sum(settlement_count), 0) as total_locations')
            ->selectRaw('coalesce(sum(area_size), 0) as total_area')
            ->selectRaw('count(*) as total_records')
            ->selectRaw('count(distinct village) as total_villages')
            ->selectRaw('count(distinct district) as total_districts')
            ->selectRaw('count(distinct regency) as total_regencies')
            ->first();

        return [
            'total_locations' => (int) ($row->total_locations ?? 0),
            'total_area_ha' => round((float) ($row->total_area ?? 0), 4),
            'total_records' => (int) ($row->total_records ?? 0),
            'total_villages' => (int) ($row->total_villages ?? 0),
            'total_districts' => (int) ($row->total_districts ?? 0),
            'total_regencies' => (int) ($row->total_regencies ?? 0),
        ];
    }

    /**
     * Total per tahun (total_records = jumlah data per tahun).
     *
     * @return array<int, array{year: int, total_locations: int, total_area_ha: float, total_records: int}>
     */
    public static function byYear(Builder $query): array
    {
        return (clone $query)
            ->select('year')
            ->selectRaw('coalesce(sum(settlement_count), 0) as total_locations')
            ->selectRaw('coalesce(sum(area_size), 0) as total_area')
            ->selectRaw('count(*) as total_records')
            ->whereNotNull('year')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(fn ($r) => [
                'year' => (int) $r->year,
                'total_locations' => (int) $r->total_locations,
                'total_area_ha' => round((float) $r->total_area, 4),
                'total_records' => (int) $r->total_records,
            ])
            ->values()->all();
    }

    /**
     * Total per kabupaten/kota.
     *
     * @return array<int, array{regency: string, total_locations: int, total_area_ha: float, total_records: int}>
     */
    public static function byRegency(Builder $query): array
    {
        return (clone $query)
            ->select('regency')
            ->selectRaw('coalesce(sum(settlement_count), 0) as total_locations')
            ->selectRaw('coalesce(sum(area_size), 0) as total_area')
            ->selectRaw('count(*) as total_records')
            ->whereNotNull('regency')
            ->groupBy('regency')
            ->orderByDesc('total_area')
            ->get()
            ->map(fn ($r) => [
                'regency' => (string) $r->regency,
                'total_locations' => (int) $r->total_locations,
                'total_area_ha' => round((float) $r->total_area, 4),
                'total_records' => (int) $r->total_records,
            ])
            ->values()->all();
    }

    /**
     * Ranking wilayah berdasarkan total lokasi.
     *
     * @return array<int, array{name: string, total_locations: int, total_area_ha: float}>
     */
    public static function ranking(Builder $query, string $field, int $limit = 10): array
    {
        return (clone $query)
            ->select($field)
            ->selectRaw('coalesce(sum(settlement_count), 0) as total_locations')
            ->selectRaw('coalesce(sum(area_size), 0) as total_area')
            ->whereNotNull($field)
            ->groupBy($field)
            ->orderByDesc('total_area')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => (string) $r->{$field},
                'total_locations' => (int) $r->total_locations,
                'total_area_ha' => round((float) $r->total_area, 4),
            ])
            ->values()->all();
    }

    /**
     * Perubahan total lokasi & luas dari tahun sebelumnya.
     * Query diberikan tanpa filter tahun agar perbandingan antar tahun benar.
     *
     * @return array{current_year: ?int, previous_year: ?int, locations_change: int, area_change: float, locations_pct: float, area_pct: float}
     */
    protected static function yearChange(Builder $query, ?int $year = null): array
    {
        $currentYear = $year ?? (int) ((clone $query)->max('year') ?: 0);
        $previousYear = $currentYear > 0 ? self::previousYear($currentYear) : null;

        $currentLocations = (int) (clone $query)->where('year', $currentYear)->sum('settlement_count');
        $previousLocations = $previousYear !== null
            ? (int) (clone $query)->where('year', $previousYear)->sum('settlement_count')
            : 0;
        $currentArea = round((float) (clone $query)->where('year', $currentYear)->sum('area_size'), 4);
        $previousArea = $previousYear !== null
            ? round((float) (clone $query)->where('year', $previousYear)->sum('area_size'), 4)
            : 0.0;

        return [
            'current_year' => $currentYear,
            'previous_year' => $previousYear,
            'locations_change' => $currentLocations - $previousLocations,
            'area_change' => round($currentArea - $previousArea, 4),
            'locations_pct' => $previousLocations > 0
                ? round((($currentLocations - $previousLocations) / $previousLocations) * 100, 2)
                : 0.0,
            'area_pct' => $previousArea > 0
                ? round((($currentArea - $previousArea) / $previousArea) * 100, 2)
                : 0.0,
        ];
    }

    protected static function previousYear(int $year): ?int
    {
        $years = self::availableYears();

        return $years->filter(fn (int $y) => $y < $year)->max() ?: null;
    }

    /**
     * Flush semua cache statistik (dipanggil saat data berubah via admin).
     */
    public static function flushCache(): void
    {
        Cache::forget('filter_years');
        Cache::forget('filter_regencies');
        Cache::forget('filter_districts');
        Cache::forget('filter_villages');
        Cache::forget('home_stats');
        Cache::forget('province_first_id');
        Cache::forget('filter_regencies_list');
        Cache::forget('filter_districts_list');
        Cache::forget('filter_villages_list');
        Cache::forget('available_filters');
        Cache::forget('file_years');
        // Cache overview/area pakai prefix dinamis, flush via tag bila pakai
        // Cache Tags. Tanpa tags, biarkan TTL habis atau gunakan wildcard flush.
    }
}
