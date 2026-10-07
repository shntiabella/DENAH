<?php

namespace App\Support;

/**
 * Menghitung luas (km²) dari GeoJSON memakai shoelace formula
 * pada proyeksi equirectangular sederhana. Akurat cukup untuk preview/admin.
 */
class GeoAreaCalculator
{
    /**
     * Hitung luas (km²) dari GeoJSON (FeatureCollection / Feature / geometry).
     */
    public static function areaKm2(?array $data): float
    {
        if ($data === null) {
            return 0.0;
        }
        $polygons = [];
        if (($data['type'] ?? null) === 'FeatureCollection') {
            foreach ($data['features'] ?? [] as $feature) {
                self::collectPolygons($feature['geometry'] ?? [], $polygons);
            }
        } elseif (($data['type'] ?? null) === 'Feature') {
            self::collectPolygons($data['geometry'] ?? [], $polygons);
        } else {
            self::collectPolygons($data, $polygons);
        }
        if ($polygons === []) {
            return 0.0;
        }
        $totalM2 = 0.0;
        foreach ($polygons as $polygon) {
            $outer = $polygon[0] ?? [];
            $area = self::ringAreaM2($outer);
            for ($i = 1, $iMax = count($polygon); $i < $iMax; $i++) {
                $area -= self::ringAreaM2($polygon[$i]);
            }
            $totalM2 += abs($area);
        }

        return round($totalM2 / 1_000_000, 4);
    }

    /**
     * Hitung luas (Ha) dari GeoJSON (hasil areaKm2 dikonversi 1 km² = 100 Ha).
     *
     * @param  array<string, mixed>|null  $data
     */
    public static function areaHa(?array $data): float
    {
        return round(self::areaKm2($data) * 100, 4);
    }

    protected static function collectPolygons(array $geometry, array &$polygons): void
    {
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? null;
        switch ($type) {
            case 'Polygon':
                if (is_array($coords)) {
                    $polygons[] = $coords;
                }
                break;
            case 'MultiPolygon':
                if (is_array($coords)) {
                    foreach ($coords as $poly) {
                        $polygons[] = $poly;
                    }
                }
                break;
            case 'GeometryCollection':
                foreach ($geometry['geometries'] ?? [] as $g) {
                    self::collectPolygons($g, $polygons);
                }
                break;
        }
    }

    /**
     * @param  array<int, array{0: float, 1: float}>  $ring
     */
    protected static function ringAreaM2(array $ring): float
    {
        $n = count($ring);
        if ($n < 4) {
            return 0.0;
        }
        // pastikan ring tertutup
        if ($ring[0] !== end($ring)) {
            $ring[] = $ring[0];
            $n = count($ring);
        }
        if ($n < 4) {
            return 0.0;
        }
        $R = 6_378_137.0;
        // latitude referensi rerata untuk proyeksi equirectangular
        $sumLat = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            $sumLat += $ring[$i][1];
        }
        $lat0 = deg2rad($sumLat / ($n - 1));
        $cosLat0 = cos($lat0);

        $total = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            $lon1 = deg2rad($ring[$i][0]);
            $lat1 = deg2rad($ring[$i][1]);
            $lon2 = deg2rad($ring[$i + 1][0]);
            $lat2 = deg2rad($ring[$i + 1][1]);
            $x1 = $R * $cosLat0 * $lon1;
            $y1 = $R * $lat1;
            $x2 = $R * $cosLat0 * $lon2;
            $y2 = $R * $lat2;
            $total += ($x1 * $y2 - $x2 * $y1);
        }

        return abs($total) / 2.0;
    }

    /**
     * Cek apakah koordinat berada dalam rentang wajar Indonesia.
     *
     * @return array{ok: bool, message: string}
     */
    public static function checkIndonesiaRange(?array $data): array
    {
        if ($data === null) {
            return ['ok' => true, 'message' => ''];
        }
        $bounds = self::bounds($data);
        if ($bounds === null) {
            return ['ok' => true, 'message' => ''];
        }

        return self::rangeCheck($bounds['min_lat'], $bounds['max_lat'], $bounds['min_lon'], $bounds['max_lon']);
    }

    /**
     * Batas lat/lon dari satu Feature (atau geometry) tanpa perlu
     * menyimpan seluruh koordinat, cocok untuk pemrosesan bertahap.
     *
     * @param  array<string, mixed>  $feature
     * @return array{min_lat: float, max_lat: float, min_lon: float, max_lon: float}|null
     */
    public static function featureBounds(array $feature): ?array
    {
        $minLat = INF;
        $maxLat = -INF;
        $minLon = INF;
        $maxLon = -INF;
        self::collectBounds($feature, $minLat, $maxLat, $minLon, $maxLon);
        if ($minLat === INF) {
            return null;
        }

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lon' => $minLon,
            'max_lon' => $maxLon,
        ];
    }

    /**
     * Evaluasi rentang lat/lon terhadap bounding box Indonesia.
     *
     * @return array{ok: bool, message: string}
     */
    public static function rangeCheck(float $minLat, float $maxLat, float $minLon, float $maxLon): array
    {
        $inLat = $minLat >= -11 && $maxLat <= 6;
        $inLon = $minLon >= 95 && $maxLon <= 141;
        if ($inLat && $inLon) {
            return ['ok' => true, 'message' => 'Koordinat dalam rentang wilayah Indonesia.'];
        }

        return ['ok' => false, 'message' => sprintf(
            'Peringatan: koordinat di luar rentang wajar Indonesia (lat %s..%s, lon %s..%s). Data tetap dapat disimpan.',
            round($minLat, 4), round($maxLat, 4), round($minLon, 4), round($maxLon, 4),
        )];
    }

    /**
     * Batas lat/lon dari seluruh dokumen GeoJSON.
     *
     * @param  array<string, mixed>  $data
     * @return array{min_lat: float, max_lat: float, min_lon: float, max_lon: float}|null
     */
    protected static function bounds(array $data): ?array
    {
        $minLat = INF;
        $maxLat = -INF;
        $minLon = INF;
        $maxLon = -INF;
        self::collectBounds($data, $minLat, $maxLat, $minLon, $maxLon);
        if ($minLat === INF) {
            return null;
        }

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lon' => $minLon,
            'max_lon' => $maxLon,
        ];
    }

    /**
     * Menelusuri struktur GeoJSON dan memperbarui running min/max lat/lon.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function collectBounds(
        array $data,
        float &$minLat,
        float &$maxLat,
        float &$minLon,
        float &$maxLon,
    ): void {
        if (($data['type'] ?? null) === 'FeatureCollection') {
            foreach ($data['features'] ?? [] as $f) {
                if (is_array($f)) {
                    self::collectBounds($f, $minLat, $maxLat, $minLon, $maxLon);
                }
            }

            return;
        }
        if (($data['type'] ?? null) === 'Feature') {
            $geometry = $data['geometry'] ?? null;
            if (is_array($geometry)) {
                self::collectBounds($geometry, $minLat, $maxLat, $minLon, $maxLon);
            }

            return;
        }
        $type = $data['type'] ?? null;
        if ($type === 'GeometryCollection') {
            foreach ($data['geometries'] ?? [] as $g) {
                if (is_array($g)) {
                    self::collectBounds($g, $minLat, $maxLat, $minLon, $maxLon);
                }
            }

            return;
        }
        $coords = $data['coordinates'] ?? null;
        if (is_array($coords)) {
            self::walkCoordsBounds($coords, $minLat, $maxLat, $minLon, $maxLon);
        }
    }

    /**
     * Memperbarui running min/max lat/lon untuk satu koordinat.
     *
     * @param  mixed  $coords
     */
    protected static function walkCoordsBounds(
        $coords,
        float &$minLat,
        float &$maxLat,
        float &$minLon,
        float &$maxLon,
    ): void {
        if (! is_array($coords)) {
            return;
        }
        if (count($coords) >= 2 && is_numeric($coords[0] ?? null) && is_numeric($coords[1] ?? null)) {
            $lon = (float) $coords[0];
            $lat = (float) $coords[1];
            $minLat = min($minLat, $lat);
            $maxLat = max($maxLat, $lat);
            $minLon = min($minLon, $lon);
            $maxLon = max($maxLon, $lon);

            return;
        }
        foreach ($coords as $c) {
            self::walkCoordsBounds($c, $minLat, $maxLat, $minLon, $maxLon);
        }
    }
}
