<?php

namespace App\Livewire\Admin;

use Carbon\CarbonImmutable;
use App\Models\ActivityLog;
use App\Models\AdministrativeArea;
use App\Models\SettlementArea;
use App\Support\SettlementStatistics;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Dashboard — DENAH CMS')]
class Dashboard extends AdminComponent
{
    public string $pageTitle = 'Dashboard';

    /** @return array<int, array{label: string, url?: string}> */
    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard'],
        ];
    }

    public function render()
    {
        $totalRecords = SettlementArea::count();
        $totalPublished = SettlementArea::where('status', 'published')->count();
        $totalLokasi = (int) SettlementArea::where('status', 'published')->sum('settlement_count');
        $totalLuas = (float) SettlementArea::where('status', 'published')->sum('area_size');
        $totalWilayah = AdministrativeArea::count();
        $totalDesa = AdministrativeArea::where('level', AdministrativeArea::LEVEL_VILLAGES)->count();
        $totalKecamatan = AdministrativeArea::where('level', AdministrativeArea::LEVEL_DISTRICTS)->count();
        $totalKabupaten = AdministrativeArea::where('level', AdministrativeArea::LEVEL_REGENCIES)->count();
        $terakhirUpdate = SettlementArea::query()->max('updated_at');
        $terakhirUpdate = $terakhirUpdate
            ? CarbonImmutable::parse($terakhirUpdate)->diffForHumans()
            : '-';

        $perTahun = SettlementStatistics::byYear(
            SettlementArea::where('status', 'published')
        );
        $maxTahun = collect($perTahun)->max('total_area_ha') ?: 1;

        $perRegency = SettlementArea::where('status', 'published')
            ->select('regency', DB::raw('count(*) as total'), DB::raw('coalesce(sum(area_size), 0) as total_area_ha'))
            ->whereNotNull('regency')
            ->groupBy('regency')
            ->orderByDesc('total_area_ha')
            ->limit(8)
            ->get();
        $maxRegency = $perRegency->max('total_area_ha') ?: 1;

        $aktivitas = ActivityLog::with('user')->latest()->limit(8)->get();

        return view('livewire.admin.dashboard', [
            'totalRecords' => $totalRecords,
            'totalPublished' => $totalPublished,
            'totalLokasi' => $totalLokasi,
            'totalLuas' => $totalLuas,
            'totalWilayah' => $totalWilayah,
            'totalDesa' => $totalDesa,
            'totalKecamatan' => $totalKecamatan,
            'totalKabupaten' => $totalKabupaten,
            'terakhirUpdate' => $terakhirUpdate,
            'perTahun' => $perTahun,
            'maxTahun' => $maxTahun,
            'perRegency' => $perRegency,
            'maxRegency' => $maxRegency,
            'aktivitas' => $aktivitas,
        ]);
    }
}
