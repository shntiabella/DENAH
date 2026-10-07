<?php

namespace App\Support;

/**
 * Utilitas geometri spasial ringan (tanpa ekstensi DB) untuk mencocokkan
 * koordinat titik permukiman ke dalam polygon batas administrasi.
 *
 * Koordinat dianggap berformat GeoJSON standar [lon, lat].
 * Hanya menangani geometri Polygon & MultiPolygon (poligon permukiman
 * dan batas wilayah administrasi).
 */
final class GeoPointInPolygon
{
    /**
     * Cek apakah sebuah titik [lon, lat] berada di dalam satu ring (loop tertutup).
     *
     * @param  array<int, array{0: float, 1: float}>  $ring  daftar coords
     */
    public static function pointInRing(array $ring, float $lon, float $lat): bool
    {
        $inside = false;
        $count = count($ring);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) ($ring[$i][0] ?? 0);
            $yi = (float) ($ring[$i][1] ?? 0);
            $xj = (float) ($ring[$j][0] ?? 0);
            $yj = (float) ($ring[$j][1] ?? 0);

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lon < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Cek apakah titik berada di dalam satu polygon (outer ring yang berisi
     * dan bukan di dalam lubang / inner ring).
     *
     * @param  array<int, array>  $rings  ring ke-0 = outer, sisanya lubang
     */
    public static function pointInPolygon(array $rings, float $lon, float $lat): bool
    {
        if ($rings === []) {
            return false;
        }
        if (! self::pointInRing($rings[0], $lon, $lat)) {
            return false;
        }
        for ($i = 1, $count = count($rings); $i < $count; $i++) {
            if (self::pointInRing($rings[$i], $lon, $lat)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cek apakah sebuah geometri (Polygon / MultiPolygon) memuat titik.
     *
     * @param  array<string, mixed>|null  $geometry
     */
    public static function contains(?array $geometry, float $lon, float $lat): bool
    {
        if (! is_array($geometry) || ! isset($geometry['type'], $geometry['coordinates'])) {
            return false;
        }

        $coords = $geometry['coordinates'];

        if ($geometry['type'] === 'Polygon') {
            return is_array($coords) && self::pointInPolygon($coords, $lon, $lat);
        }

        if ($geometry['type'] === 'MultiPolygon') {
            foreach ($coords as $polygon) {
                if (is_array($polygon) && self::pointInPolygon($polygon, $lon, $lat)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ambil satu titik representatif (centroid ring luar terbesar) dari sebuah
     * geometri permukiman. Mengembalikan [lon, lat] atau null bila tak dapat.
     *
     * @param  array<string, mixed>|null  $geometry
     * @return array{0: float, 1: float}|null
     */
    public static function representativePoint(?array $geometry): ?array
    {
        if (! is_array($geometry) || ! isset($geometry['type'], $geometry['coordinates'])) {
            return null;
        }

        $coords = $geometry['coordinates'];

        if ($geometry['type'] === 'Polygon') {
            $rings = $coords[0] ?? [];
        } elseif ($geometry['type'] === 'MultiPolygon') {
            // Gabung seluruh polygon, pilih outer ring terluas.
            $rings = null;
            $best = -1.0;
            foreach ($coords as $polygon) {
                if (! is_array($polygon) || $polygon === []) {
                    continue;
                }
                $outer = $polygon[0];
                $area = self::ringAreaAbs($outer);
                if ($area > $best) {
                    $best = $area;
                    $rings = $outer;
                }
            }
        } else {
            return null;
        }

        if (! is_array($rings) || $rings === []) {
            return null;
        }

        // Centroid shoelace.
        $x = 0.0;
        $y = 0.0;
        $twiceArea = 0.0;
        $count = count($rings);
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) ($rings[$i][0] ?? 0);
            $yi = (float) ($rings[$i][1] ?? 0);
            $xj = (float) ($rings[$j][0] ?? 0);
            $yj = (float) ($rings[$j][1] ?? 0);
            $cross = $xi * $yj - $xj * $yi;
            $twiceArea += $cross;
            $x += ($xi + $xj) * $cross;
            $y += ($yi + $yj) * $cross;
        }

        if (abs($twiceArea) < 1e-9) {
            return self::bboxCenter($rings);
        }

        return [
            (float) ($x / (3 * $twiceArea)),
            (float) ($y / (3 * $twiceArea)),
        ];
    }

    /**
     * Luas absolut ring (shoelace) untuk membandingkan ring terluas.
     *
     * @param  array<int, array>  $ring
     */
    private static function ringAreaAbs(array $ring): float
    {
        $sum = 0.0;
        $count = count($ring);
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) ($ring[$i][0] ?? 0);
            $yi = (float) ($ring[$i][1] ?? 0);
            $xj = (float) ($ring[$j][0] ?? 0);
            $yj = (float) ($ring[$j][1] ?? 0);
            $sum += $xi * $yj - $xj * $yi;
        }

        return abs($sum) / 2;
    }

    /**
     * Pusat kotak pembatas (jadikan jaminan bila centroid degenerasi).
     *
     * @param  array<int, array>  $ring
     * @return array{0: float, 1: float}
     */
    private static function bboxCenter(array $ring): array
    {
        $minLon = $minLat = INF;
        $maxLon = $maxLat = -INF;
        foreach ($ring as $p) {
            $lon = (float) ($p[0] ?? 0);
            $lat = (float) ($p[1] ?? 0);
            $minLon = min($minLon, $lon);
            $maxLon = max($maxLon, $lon);
            $minLat = min($minLat, $lat);
            $maxLat = max($maxLat, $lat);
        }

        return [($minLon + $maxLon) / 2, ($minLat + $maxLat) / 2];
    }
}