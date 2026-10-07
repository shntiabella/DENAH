<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeArea;
use App\Models\SettlementArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettlementAreaController extends Controller
{
    /**
     * GET /api/settlement-areas?year=&regency_id=&district_id=&village_id=
     * Mengembalikan satu FeatureCollection GeoJSON gabungan
     * dari seluruh wilayah berstatus published, dengan properties
     * tambahan untuk popup peta publik (nama, luas, dsb.).
     */
    public function index(Request $request): JsonResponse
    {
        $query = SettlementArea::query()
            ->where('status', 'published')
            ->whereNotNull('geojson_data');

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        // Filter wilayah via id (diselesaikan ke nama untuk konsistensi
        // dengan kolom province/regency/district/village pada settlement_areas).
        foreach (['regency' => 'regency_id', 'district' => 'district_id', 'village' => 'village_id'] as $column => $param) {
            if (! $request->filled($param)) {
                continue;
            }
            $name = AdministrativeArea::whereKey($request->integer($param))->value('name');
            if ($name !== null) {
                $query->where($column, $name);
            }
        }

        $areas = $query->get();

        $features = [];
        foreach ($areas as $area) {
            $geo = $area->geojson_data;
            if (! is_array($geo)) {
                continue;
            }

            // Normalisasi: bila FeatureCollection, ambil tiap feature-nya
            // dan sisipkan properties DENAH; bila Feature tunggal, perbaiki properties.
            $type = $geo['type'] ?? null;
            if ($type === 'FeatureCollection') {
                foreach ($geo['features'] ?? [] as $feature) {
                    $features[] = $this->enrichFeature($feature, $area);
                }
            } elseif ($type === 'Feature') {
                $features[] = $this->enrichFeature($geo, $area);
            }
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * GET /api/settlement-areas/{id}
     * Detail satu wilayah (untuk popup/klik di peta).
     */
    public function show(SettlementArea $settlementArea): JsonResponse
    {
        if (! $settlementArea->isPublished()) {
            abort(404, 'Wilayah tidak ditemukan atau belum dipublikasikan.');
        }

        $geo = $settlementArea->geojson_data;
        $feature = is_array($geo) && ($geo['type'] ?? null) === 'Feature'
            ? $this->enrichFeature($geo, $settlementArea)
            : $this->enrichFeature(['type' => 'Feature', 'geometry' => $geo, 'properties' => []], $settlementArea);

        return response()->json($feature);
    }

    /**
     * Sisipkan properties DENAH ke sebuah GeoJSON Feature.
     *
     * @param array $feature
     * @return array
     */
    protected function enrichFeature(array $feature, SettlementArea $area): array
    {
        $properties = $feature['properties'] ?? [];
        if (! is_array($properties)) {
            $properties = [];
        }

        $properties = array_merge($properties, [
            'denah_id' => $area->id,
            'denah_name' => $area->name,
            'denah_area_ha' => (float) $area->area_size,
            'denah_legend_color' => '#658B38',
            'denah_description' => $area->description,
            'denah_status' => $area->status,
        ]);

        $feature['properties'] = $properties;

        return $feature;
    }
}
