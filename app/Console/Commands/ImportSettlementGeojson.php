<?php

namespace App\Console\Commands;

use App\Support\GeoJsonAttributeMapper;
use App\Support\GeoJsonHelper;
use App\Support\GeoJsonStreamReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportSettlementGeojson extends Command
{
    protected $signature = 'denah:import-geojson
        {--years=2021,2022,2023,2024,2025 : Tahun yang diimpor, dipisah koma}
        {--path= : Direktori berisi file data_YYYY.geojson (default: public/data)}
        {--chunk=500 : Jumlah baris per batch insert}
        {--no-replace : Jangan hapus data tahun terkait sebelum mengimpor}';

    protected $description = 'Import seluruh feature dari data_YYYY.geojson (public/data) ke tabel settlement_areas tanpa upload browser.';

    public function handle(): int
    {
        $years = array_values(array_filter(array_map('intval', explode(',', $this->option('years')))));
        if ($years === []) {
            $this->error('Tidak ada tahun valid pada opsi --years.');

            return self::FAILURE;
        }

        $dir = $this->option('path') ?: public_path('data');
        if (! is_dir($dir)) {
            $this->error("Direktori tidak ditemukan: {$dir}");

            return self::FAILURE;
        }

        $replace = ! (bool) $this->option('no-replace');

        foreach ($years as $year) {
            $file = $dir.'/data_'.$year.'.geojson';
            if (! is_file($file)) {
                $this->error("File tidak ditemukan: {$file}");

                continue;
            }

            $imported = $this->importYear($year, $file, $replace);
            $this->line('');
            $this->info("Tahun {$year}: {$imported} feature diimpor.");
        }

        return self::SUCCESS;
    }

    protected function importYear(int $year, string $file, bool $replace): int
    {
        if ($replace) {
            $deleted = DB::table('settlement_areas')->where('year', $year)->delete();
            $this->info("Tahun {$year}: menghapus {$deleted} data lama.");
        }

        $reader = new GeoJsonStreamReader($file);
        if (! $reader->isFeatureCollection()) {
            throw new RuntimeException("File {$file} bukan FeatureCollection.");
        }

        $sequence = 0;
        $imported = 0;
        $rows = [];

        foreach ($reader->features() as $feature) {
            if (! is_array($feature)) {
                continue;
            }
            $check = GeoJsonHelper::validateFeature($feature);
            if (! $check['valid']) {
                $this->warn("Feature #{$sequence} dilewati: {$check['message']}");

                continue;
            }

            $sequence++;

            // Hitung luas (Ha) per feature; bila properti area_ha tidak tersedia,
            // GeoJsonAttributeMapper::areaHa() menghitungnya dinamis dari geometri
            // (shoelace formula) sebelum diagregasi per Kabupaten/Kota & Per Tahun.
            $areaHa = round(GeoJsonAttributeMapper::areaHa($feature), 4);

            // FILTERING NOISE: lewati polygon berluas < 10 m² (= 0,001 Ha)
            // agar angka 'Total Lokasi' tidak membengkak hingga jutaan.
            if ($areaHa < 0.001) {
                $this->warn("Feature #{$sequence} dilewati: luas {$areaHa} Ha (< 10 m²).");

                continue;
            }

            $rows[] = [
                'name' => mb_substr(GeoJsonAttributeMapper::lokasiName($feature) ?? sprintf('Permukiman %d #%06d', $year, $sequence), 0, 120),
                'year' => $year,
                'province' => 'Bengkulu',
                'regency' => GeoJsonAttributeMapper::regency($feature),
                'district' => GeoJsonAttributeMapper::district($feature),
                'village' => GeoJsonAttributeMapper::village($feature),
                'settlement_count' => 1,
                'area_size' => $areaHa,
                'geojson_file' => null,
                'geojson_data' => json_encode($feature, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status' => 'published',
                'description' => 'Diimpor dari data_'.$year.'.geojson.',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($rows) >= (int) $this->option('chunk')) {
                DB::table('settlement_areas')->insert($rows);
                $imported += count($rows);
                $rows = [];
                $this->output->write('.');
            }
        }

        if ($rows !== []) {
            DB::table('settlement_areas')->insert($rows);
            $imported += count($rows);
            $this->output->write('.');
        }

        return $imported;
    }
}
