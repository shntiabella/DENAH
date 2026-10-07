<?php

namespace App\Livewire\Public;

use App\Models\AdministrativeArea;
use App\Models\Setting;
use App\Support\SettlementStatistics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman Peta & Data publik.
 *
 * Komponen ini hanya menyediakan konfigurasi awal untuk aplikasi peta
 * (Leaflet) yang berjalan di sisi klien:
 *  - daftar tahun gabungan dari file GeoJSON di public/data + tabel statistik,
 *  - daftar klasifikasi permukiman (warna legenda),
 *  - provinsi sebagai wilayah awal yang dipilih.
 *
 * Semua data dropdown di-cache 24 jam agar dropdown filter tidak pernah
 * bernilai null saat server di-restart.
 */
#[Layout('layouts.public')]
#[Title('Peta & Data — DENAH')]
class Peta extends Component
{
    /**
     * Tahun yang memiliki file raster GeoJSON di public/data (data_YYYY.geojson).
     *
     * @return array<int, int>
     */
    public function getFileYearsProperty(): array
    {
        return Cache::remember('file_years', 86400, function () {
            $years = [];
            foreach (glob(public_path('data/data_*.geojson')) ?: [] as $path) {
                if (preg_match('/data_(\d{4})\.geojson$/', (string) $path, $m)) {
                    $years[] = (int) $m[1];
                }
            }
            sort($years);

            return $years;
        });
    }

    /**
     * Gabungan tahun dari file raster GeoJSON dan tahun data statistik (database).
     *
     * @return Collection<int, int>
     */
    public function getYearsProperty(): Collection
    {
        return collect($this->fileYears)
            ->merge(SettlementStatistics::availableYears())
            ->map(fn ($y) => (int) $y)
            ->unique()
            ->sort()
            ->values();
    }

    public function getProvinceProperty(): ?AdministrativeArea
    {
        $id = Cache::remember('province_first_id', 86400, function () {
            return AdministrativeArea::where('level', AdministrativeArea::LEVEL_PROVINCE)->value('id');
        });

        return $id ? AdministrativeArea::find($id) : null;
    }

    public function render()
    {
        $province = $this->province;
        $fileYears = $this->fileYears;

        return view('livewire.public.peta', [
            'pageBackground' => Setting::get('peta_background_image'),
            'years' => $this->years,
            'fileYears' => $fileYears,
            'province' => $province,
            // Konfigurasi awal untuk JavaScript peta (diserialkan via @js di view).
            'jsConfig' => [
                'province' => $province ? [
                    'id' => $province->id,
                    'name' => $province->name,
                    'lat' => $province->lat,
                    'lng' => $province->lng,
                ] : null,
                'fileYears' => $fileYears,
                'latestFileYear' => $fileYears !== [] ? max($fileYears) : null,
                'endpoints' => [
                    'areas' => url('/api/administrative-areas'),
                    'stats' => url('/api/settlement-stats'),
                    // Endpoint chunked GeoJSON per wilayah (lebih cepat dari file raksasa).
                    'geojsonChunk' => url('/api/settlement-geojson-chunk'),
                    // Pola URL file raster publik; {year} diganti di JavaScript (fallback).
                    'raster' => url('/data/data_{year}.geojson'),
                ],
            ],
        ]);
    }
}
