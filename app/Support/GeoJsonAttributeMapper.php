<?php

namespace App\Support;

/**
 * Memetakan atribut properti GeoJSON ke kolom database settlement_areas
 * dengan mendukung beragam penamaan key sumber (BIG/RBI, BPS, dsb.).
 * Seluruh pencarian key dilakukan case-insensitive.
 */
class GeoJsonAttributeMapper
{
    private const REGENCY_KEYS = [
        'WADMKK', 'WADMK', 'KABUPATEN', 'KABUPATEN_KOTA', 'KAB_KOTA', 'KABKOT', 'KAB', 'REGENCY',
    ];

    private const DISTRICT_KEYS = [
        'WADMKC', 'KECAMATAN', 'KECAMATAN_NAMA', 'KEC', 'DISTRICT', 'DISTRCT',
    ];

    private const VILLAGE_KEYS = [
        'WADMD', 'DESA', 'DESA_KELURAHAN', 'NAMA_DESA', 'KELURAHAN', 'KEL', 'VILLAGE', 'NAMOBJ',
    ];

    private const LOKASI_KEYS = [
        'NAMA_LOKASI', 'NAMA_KAWASAN', 'LOKASI', 'KAWASAN', 'NAMOBJ', 'NAMA_OBJ',
        'NAMA_PERMUKIMAN', 'PERMUKIMAN', 'LOKASI_KAWASAN', 'WILAYAH', 'NAMA', 'NAME',
    ];

    /**
     * Nama kabupaten/kota dari properti GeoJSON.
     *
     * @param  array<string, mixed>  $feature
     */
    public static function regency(array $feature): ?string
    {
        return self::firstProperty(self::properties($feature), self::REGENCY_KEYS);
    }

    /**
     * Nama kecamatan dari properti GeoJSON.
     *
     * @param  array<string, mixed>  $feature
     */
    public static function district(array $feature): ?string
    {
        return self::firstProperty(self::properties($feature), self::DISTRICT_KEYS);
    }

    /**
     * Nama desa/kelurahan dari properti GeoJSON.
     *
     * @param  array<string, mixed>  $feature
     */
    public static function village(array $feature): ?string
    {
        return self::firstProperty(self::properties($feature), self::VILLAGE_KEYS);
    }

    /**
     * Nama lokasi/kawasan permukiman dari properti GeoJSON.
     *
     * @param  array<string, mixed>  $feature
     */
    public static function lokasiName(array $feature): ?string
    {
        return self::firstProperty(self::properties($feature), self::LOKASI_KEYS);
    }

    /**
     * Luas fitur dalam hektar (Ha).
     *
     * Prioritas: LUAS_HA (Ha) -> LUAS (m²) -> LUAS_KM2 (km²) ->
     * hitung ulang dari geometri (shoelace, diubah km² -> Ha).
     *
     * @param  array<string, mixed>  $feature
     */
    public static function areaHa(array $feature): float
    {
        $props = self::properties($feature);

        $ha = self::firstNumeric($props, ['LUAS_HA', 'AREA_HA', 'HA']);
        if ($ha !== null) {
            return (float) $ha;
        }

        $m2 = self::firstNumeric($props, ['LUAS', 'LUAS_M2', 'AREA_M2', 'LUAS_M2']);
        if ($m2 !== null) {
            return (float) $m2 / 10_000;
        }

        $km2 = self::firstNumeric($props, ['LUAS_KM2', 'AREA_KM2']);
        if ($km2 !== null) {
            return (float) $km2 * 100;
        }

        return GeoAreaCalculator::areaHa($feature);
    }

    /**
     * @param  array<string, mixed>  $feature
     * @return array<string, mixed>
     */
    private static function properties(array $feature): array
    {
        $props = $feature['properties'] ?? null;

        return is_array($props) ? $props : [];
    }

    /**
     * Nilai string pertama dari daftar key (urutan = prioritas),
     * dicocokkan case-insensitive.
     *
     * @param  array<string, mixed>  $properties
     * @param  array<int, string>  $keys
     */
    private static function firstProperty(array $properties, array $keys): ?string
    {
        foreach ($keys as $key) {
            foreach ($properties as $name => $value) {
                if (strcasecmp((string) $name, $key) !== 0) {
                    continue;
                }
                if ($value === null || is_array($value) || trim((string) $value) === '') {
                    continue;
                }

                return trim((string) $value);
            }
        }

        return null;
    }

    /**
     * Nilai numerik pertama dari daftar key (urutan = prioritas).
     *
     * @param  array<string, mixed>  $properties
     * @param  array<int, string>  $keys
     */
    private static function firstNumeric(array $properties, array $keys): ?float
    {
        foreach ($keys as $key) {
            foreach ($properties as $name => $value) {
                if (strcasecmp((string) $name, $key) !== 0) {
                    continue;
                }
                if (! is_numeric($value)) {
                    continue;
                }

                return (float) $value;
            }
        }

        return null;
    }
}
