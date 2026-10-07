<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed data induk (master) wilayah administrasi Provinsi Bengkulu.
 *
 * - Menggunakan DB::table('administrative_areas')->updateOrInsert() sehingga idempoten:
 *   baris yang sudah ada TIDAK dihapus/dipecah, dan kolom identitas (code/level/name/parent_id)
 *   diperbarui hanya bila diperlukan. Kolom geojson/lat/lng yang sudah ada TIDAK ditimpa,
 *   agar hasil impor GeoJSON sebelumnya tetap dipertahankan.
 *
 * - Provinsi + kabupaten/kota + kecamatan dimuat dari file data JSON permanen.
 *   Desa/kelurahan (jumlah besar) dimuat dari file opsional bila tersedia, agar seeder
 *   tetap ringan dan tetap aman untuk dijalankan ulang berkali-kali.
 */
class AdministrativeAreaSeeder extends Seeder
{
    private const PROVINCE = '17';
    private const PROVINCE_NAME = 'Bengkulu';

    private const DATA_FILE = 'bengkulu_administrative.json';
    private const VILLAGES_FILE = 'bengkulu_villages.json';

    public function run(): void
    {
        $regencies = $this->loadJson(self::DATA_FILE);

        DB::transaction(function () use ($regencies) {
            $province = $this->upsertProvince();

            foreach ($regencies as $regency) {
                $regencyId = $this->upsertRegency($regency, $province);

                foreach ($regency['districts'] ?? [] as $district) {
                    $this->upsertDistrict($district, $regencyId, $regency['code']);
                }
            }
        });

        $this->upsertVillages();
    }

    private function upsertProvince(): int
    {
        DB::table('administrative_areas')->updateOrInsert(
            ['level' => 'provinsi', 'code' => self::PROVINCE],
            [
                'name' => self::PROVINCE_NAME,
                'parent_id' => null,
                'updated_at' => now(),
            ]
        );

        $this->command?->info("Provinsi: ".self::PROVINCE_NAME);

        return (int) DB::table('administrative_areas')
            ->where('level', 'provinsi')
            ->where('code', self::PROVINCE)
            ->value('id');
    }

    /**
     * @param  array{code: string, name: string}  $regency
     */
    private function upsertRegency(array $regency, int $provinceId): int
    {
        list($code, $name) = [$regency['code'], $regency['name']];

        DB::table('administrative_areas')->updateOrInsert(
            ['level' => 'kabupaten', 'parent_id' => $provinceId, 'name' => $name],
            ['code' => $code, 'level' => 'kabupaten', 'parent_id' => $provinceId, 'updated_at' => now()]
        );

        $this->command?->line("  - Kabupaten/Kota: {$name}");

        return (int) DB::table('administrative_areas')
            ->where('level', 'kabupaten')
            ->where('parent_id', $provinceId)
            ->where('name', $name)
            ->value('id');
    }

    /**
     * @param  array{code: string, name: string}  $district
     */
    private function upsertDistrict(array $district, int $regencyId, string $regencyCode): int
    {
        $code = $regencyCode.'.'.$district['code'];
        $name = $district['name'];

        DB::table('administrative_areas')->updateOrInsert(
            ['level' => 'kecamatan', 'parent_id' => $regencyId, 'name' => $name],
            ['code' => $code, 'level' => 'kecamatan', 'parent_id' => $regencyId, 'updated_at' => now()]
        );

        $this->command?->line("    - Kecamatan: {$name}");

        return (int) DB::table('administrative_areas')
            ->where('level', 'kecamatan')
            ->where('parent_id', $regencyId)
            ->where('name', $name)
            ->value('id');
    }

    private function upsertVillages(): void
    {
        $path = database_path('seeders/data/'.self::VILLAGES_FILE);
        if (! is_file($path)) {
            $this->command?->warn(
                'File desa opsional tidak ditemukan — lewati: '.self::VILLAGES_FILE
            );

            return;
        }

        $villages = $this->loadJson(self::VILLAGES_FILE);
        $districtIndex = DB::table('administrative_areas')
            ->where('level', 'kecamatan')
            ->get(['id', 'code'])
            ->keyBy('code');

        $total = 0;
        foreach ($villages as $item) {
            $parent = $districtIndex[$item['district_code']] ?? null;
            if ($parent === null) {
                continue;
            }

            DB::table('administrative_areas')->updateOrInsert(
                ['level' => 'desa', 'parent_id' => $parent->id, 'name' => $item['name']],
                [
                    'code' => $item['code'] ?? null,
                    'level' => 'desa',
                    'parent_id' => $parent->id,
                    'updated_at' => now(),
                ]
            );
            $total++;
        }

        $this->command?->info("Desa/Kelurahan terproses: {$total}");
    }

    /**
     * @return array<int, mixed>
     */
    private function loadJson(string $file): array
    {
        $path = database_path('seeders/data/'.$file);
        if (! is_file($path)) {
            throw new \RuntimeException("File data seeder tidak ditemukan: {$path}");
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            throw new \RuntimeException("File data seeder tidak valid (JSON): {$path}");
        }

        return $data;
    }
}