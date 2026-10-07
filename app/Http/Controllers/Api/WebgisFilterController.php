<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeArea;
use App\Models\SettlementArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Endpoints for the dependent (cascade) territory filter on the public map,
 * plus one real-time statistics endpoint.
 *
 * IMPORTANT (Option A — no migration / no structure change):
 *   The schema has NO `administrative_area_id` column anywhere. The main
 *   data table is `settlement_areas` and its rows are linked to territories
 *   by NAME, through the text columns:
 *       province / regency / district / village
 *   with the actual measurement columns:
 *       settlement_count  => total_lokasi
 *       area_size         => total_luas (Ha)
 *
 *   `administrative_areas` stores the hierarchy with a MIXED level
 *   vocabulary (provinsi|kabupaten|kecamatan|desa AND regency|district|village).
 *   This controller therefore normalizes those level strings and resolves the
 *   filter chain by following `parent_id`, so it works regardless of which
 *   vocabulary a specific row uses.
 */
class WebgisFilterController extends Controller
{
    /**
     * GET /api/get-kecamatan? kabupaten_id={kabupatenId}
     * Children of a chosen territory whose (normalized) level is a district.
     */
    public function kecamatan(Request $request): JsonResponse
    {
        $kabupatenId = $request->integer('kabupaten_id');

        $children = AdministrativeArea::query()
            ->where('parent_id', $kabupatenId)
            ->whereIn('level', ['kecamatan', 'district'])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'level']);

        return response()->json($children);
    }

    /**
     * GET /api/get-desa? kecamatan_id={kecamatanId}
     * Children of the chosen district whose (normalized) level is a village.
     */
    public function desa(Request $request): JsonResponse
    {
        $kecamatanId = $request->integer('kecamatan_id');

        $children = AdministrativeArea::query()
            ->where('parent_id', $kecamatanId)
            ->whereIn('level', ['desa', 'village'])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'level']);

        return response()->json($children);
    }
    /**
     * GET /api/get-statistics? administrative_area_id=& year=
     *    (or the alias params kabupaten_id=& kecamatan_id=& desa_id=& year=)
     *
     * Aggregates total_lokasi & total_luas for the chosen territory.
     *
     * 1) Hierarchical filter: the selected area is followed upward through the
     *    parent_id chain. Each ancestor's NAME is mapped onto the matching
     *    settlement_areas text column (provinsi|kabupaten -> province/regency,
     *    kecamatan/district -> district, desa/village -> village). The query
     *    then filters the settlement rows that belong to it.
     * 2) Optional year filter.
     * 3) Reads the real measurement columns: sum(settlement_count) and
     *    sum(area_size).
     */
    public function statistics(Request $request): JsonResponse
    {
        $year = $request->filled('year') ? $request->integer('year') : null;

        // Resolve the selected territory (by administrative_area_id, or by any
        // of the three legacy level params). Null = whole dataset.
        $area = $this->resolveArea($request);

        // Map the territory + ancestors onto settlement_areas name columns.
        $names = $area !== null ? $this->regionNames($area) : $this->emptyRegionNames();

        $query = SettlementArea::query()
            ->when($year !== null, fn (Builder $q) => $q->where('year', $year));

        foreach ([
            'province' => $names['province'],
            'regency' => $names['regency'],
            'district' => $names['district'],
            'village' => $names['village'],
        ] as $column => $value) {
            if ($value !== null) {
                $query->whereRaw('UPPER(TRIM('.$column.')) = ?', [Str::upper(Str::squish($value))]);
            }
        }

        $totals = $query
            ->selectRaw('coalesce(sum(settlement_count), 0) as total_lokasi')
            ->selectRaw('coalesce(sum(area_size), 0) as total_luas')
            ->selectRaw('count(*) as total_records')
            ->first();

        return response()->json([
            'area' => $area !== null ? [
                'id' => $area->id,
                'name' => $area->name,
                'level' => $area->level,
                'level_label' => $area->levelLabel(),
                'path' => $area->fullName(),
            ] : null,
            'filters' => [
                'administrative_area_id' => $request->filled('administrative_area_id') ? $request->integer('administrative_area_id') : null,
                'kabupaten_id' => $request->filled('kabupaten_id') ? $request->integer('kabupaten_id') : null,
                'kecamatan_id' => $request->filled('kecamatan_id') ? $request->integer('kecamatan_id') : null,
                'desa_id' => $request->filled('desa_id') ? $request->integer('desa_id') : null,
                'year' => $year,
            ],
            'totals' => [
                'total_lokasi' => (int) ($totals->total_lokasi ?? 0),
                'total_luas' => round((float) ($totals->total_luas ?? 0), 4),
                'total_records' => (int) ($totals->total_records ?? 0),
            ],
        ]);
    }

    /**
     * Resolve the selected administrative area.
     * Precedence: administrative_area_id > desa_id > kecamatan_id > kabupaten_id.
     */
    protected function resolveArea(Request $request): ?AdministrativeArea
    {
        foreach (['administrative_area_id', 'desa_id', 'kecamatan_id', 'kabupaten_id'] as $param) {
            if ($request->filled($param)) {
                return AdministrativeArea::find($request->integer($param)) ?: null;
            }
        }

        return null;
    }

    /**
     * Walk the territory + its ancestors (via parent_id) and map each name
     * onto the settlement_areas column its level filters by.
     *
     * @return array{province: string|null, regency: string|null, district: string|null, village: string|null}
     */
    protected function regionNames(AdministrativeArea $area): array
    {
        $names = $this->emptyRegionNames();

        $current = $area;
        $guard = 0;
        while ($current instanceof AdministrativeArea && $guard < 6) {
            $column = $this->columnForLevel((string) $current->level);

            if ($column !== null && $names[$column] === null) {
                $value = trim((string) $current->name);
                if ($value !== '') {
                    $names[$column] = $value;
                }
            }

            $current = $current->parent;
            $guard++;
        }

        return $names;
    }

    /** @return array{province: string|null, regency: string|null, district: string|null, village: string|null} */
    protected function emptyRegionNames(): array
    {
        return ['province' => null, 'regency' => null, 'district' => null, 'village' => null];
    }

    /**
     * Map any admin level label (both the model vocabulary and the import
     * vocabulary) to the settlement_areas column it filters by.
     */
    protected function columnForLevel(string $level): ?string
    {
        return match (Str::lower(Str::squish($level))) {
            'provinsi', 'province' => 'province',
            'kabupaten', 'regency' => 'regency',
            'kecamatan', 'district', 'distrik' => 'district',
            'desa', 'village', 'kelurahan', 'sakiawa' => 'village',
            default => null,
        };
    }
}