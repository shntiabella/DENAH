<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeArea;
use App\Models\SettlementArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdministrativeAreaController extends Controller
{
    /**
     * GET /api/administrative-areas/{level}
     * GeoJSON FeatureCollection dari batas administrasi satu level.
     * Query `parent_id` untuk memfilter anak dari wilayah tertentu.
     */
    public function index(Request $request, string $level): JsonResponse
    {
        if (! in_array($level, array_keys(AdministrativeArea::LEVELS), true)) {
            abort(404, 'Level wilayah tidak dikenal.');
        }

        $query = AdministrativeArea::where('level', $level);

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->integer('parent_id'));
        }

        // Filter desa berdasarkan kabupaten/kota induk (grandparent).
        if ($level === AdministrativeArea::LEVEL_VILLAGES && $request->filled('regency_id')) {
            $districtIds = AdministrativeArea::where('level', AdministrativeArea::LEVEL_DISTRICTS)
                ->where('parent_id', $request->integer('regency_id'))
                ->pluck('id');
            $query->whereIn('parent_id', $districtIds);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->string('name').'%');
        }

        $features = $query->orderBy('name')->get()->map(
            fn (AdministrativeArea $area) => $area->toGeoJsonFeature()
        );

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * GET /api/administrative-areas/{level}/{area}
     * Detail satu wilayah: GeoJSON Feature + ringkasan statistik permukiman.
     */
    public function show(string $level, AdministrativeArea $area): JsonResponse
    {
        if ($area->level !== $level) {
            abort(404, 'Wilayah tidak ditemukan pada level tersebut.');
        }

        $stats = SettlementArea::where('status', 'published')
            ->forAdministrativeArea($area);

        return response()->json([
            'type' => 'Feature',
            'properties' => array_merge($area->toGeoJsonFeature()['properties'], [
                'stats' => $this->aggregateStats($stats->get()),
            ]),
            'geometry' => $area->geojson,
        ]);
    }

    /** @param \Illuminate\Support\Collection<int, SettlementArea> $areas */
    protected function aggregateStats($areas): array
    {
        return [
            'total_locations' => (int) $areas->sum('settlement_count'),
            'total_area_ha' => round((float) $areas->sum('area_size'), 4),
            'total_records' => $areas->count(),
            'by_year' => $areas->groupBy('year')->map(function ($group) {
                return [
                    'year' => (int) $group->first()->year,
                    'total_locations' => (int) $group->sum('settlement_count'),
                    'total_area_ha' => round((float) $group->sum('area_size'), 4),
                ];
            })->values()->sortBy('year')->values(),
        ];
    }
}
