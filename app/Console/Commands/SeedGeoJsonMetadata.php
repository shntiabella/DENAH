<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SeedGeoJsonMetadata extends Command
{
    protected $signature = 'denah:seed-metadata
        {--force : Hapus data stat_permukiman lama sebelum seed ulang}';

    protected $description = 'Ekstrak metadata wilayah dari file GeoJSON ke database (tabel administrative_areas & stat_permukiman).';

    public function handle(): int
    {
        if ($this->option('force')) {
            DB::table('stat_permukiman')->truncate();
            $this->info('Tabel stat_permukiman dikosongkan.');
        }

        $this->call('db:seed', ['--class' => 'GeoJsonMetaDataSeeder']);

        Cache::forget('home_stats');
        Cache::forget('stat_permukiman');
        Cache::forget('filter_years');
        Cache::forget('filter_regencies');
        Cache::forget('filter_districts');
        Cache::forget('filter_villages');
        Cache::forget('province_first_id');
        Cache::forget('filter_regencies_list');
        Cache::forget('filter_districts_list');
        Cache::forget('filter_villages_list');
        Cache::forget('available_filters');
        Cache::forget('file_years');

        $this->info('Metadata berhasil di-seed dan cache di-flush.');

        return self::SUCCESS;
    }
}
