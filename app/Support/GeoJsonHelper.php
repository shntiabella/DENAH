<?php

namespace App\Support;

/**
 * Helper untuk memvalidasi & menghitung luas dari data GeoJSON.
 * Tidak bergantung library eksternal — memakai shoelace formula
 * dengan asumsi koordinat geografis (lon/lat) diproyeksikan sederhana.
 */
class GeoJsonHelper
{
    /**
     * Validasi struktur GeoJSON dasar (FeatureCollection atau Feature tunggal).
     *
     * @return array{valid: bool, message: string, data: ?array}
     */
    public static function validate(?array $data): array
    {
        if ($data === null) {
            return ['valid' => false, 'message' => 'Data GeoJSON kosong.', 'data' => null];
        }
        if (! isset($data['type'])) {
            return ['valid' => false, 'message' => 'Properti "type" tidak ditemukan pada GeoJSON.', 'data' => null];
        }
        if ($data['type'] === 'FeatureCollection') {
            $features = $data['features'] ?? null;
            if (! is_array($features)) {
                return ['valid' => false, 'message' => 'FeatureCollection tidak memiliki array "features".', 'data' => null];
            }
            foreach ($features as $feature) {
                $check = self::validateFeature($feature);
                if (! $check['valid']) {
                    return $check;
                }
            }

            return ['valid' => true, 'message' => 'FeatureCollection valid.', 'data' => $data];
        }
        if ($data['type'] === 'Feature') {
            return self::validateFeature($data);
        }

        return ['valid' => false, 'message' => 'Tipe GeoJSON tidak didukung: '.$data['type'].' (hanya Feature / FeatureCollection).', 'data' => null];
    }

    /** @return array{valid: bool, message: string, data: ?array} */
    public static function validateFeature(array $feature): array
    {
        if (($feature['type'] ?? null) !== 'Feature') {
            return ['valid' => false, 'message' => 'Feature harus memiliki type "Feature".', 'data' => null];
        }
        $geometry = $feature['geometry'] ?? null;
        if (! is_array($geometry)) {
            return ['valid' => false, 'message' => 'Feature tidak memiliki "geometry".', 'data' => null];
        }
        $geomType = $geometry['type'] ?? null;
        $allowed = ['Point', 'MultiPoint', 'LineString', 'MultiLineString', 'Polygon', 'MultiPolygon', 'GeometryCollection'];
        if (! in_array($geomType, $allowed, true)) {
            return ['valid' => false, 'message' => 'Tipe geometry tidak valid: '.(string) $geomType, 'data' => null];
        }
        if (! array_key_exists('coordinates', $geometry) && $geomType !== 'GeometryCollection') {
            return ['valid' => false, 'message' => 'Geometry tidak memiliki "coordinates".', 'data' => null];
        }

        return ['valid' => true, 'message' => 'Feature valid.', 'data' => $feature];
    }

    public static function extractName(?array $data): ?string
    {
        if ($data === null) {
            return null;
        }
        $props = null;
        if (($data['type'] ?? null) === 'FeatureCollection') {
            $first = $data['features'][0] ?? null;
            $props = $first['properties'] ?? null;
        } elseif (($data['type'] ?? null) === 'Feature') {
            $props = $data['properties'] ?? null;
        }
        if (! is_array($props)) {
            return null;
        }
        foreach (['name', 'Name', 'NAME', 'nama', 'wilayah', 'kawasan'] as $key) {
            if (! empty($props[$key]) && is_string($props[$key])) {
                return $props[$key];
            }
        }

        return null;
    }
}
