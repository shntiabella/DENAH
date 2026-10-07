<?php

namespace App\Livewire\Public;

use App\Models\AdministrativeArea;
use App\Models\Setting;
use App\Support\SettlementStatistics;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Statistik — DENAH')]
class Statistik extends Component
{
    public function render()
    {
        $cacheTTL = 86400; // 24 jam

        $regenciesData = Cache::remember('filter_regencies_list', $cacheTTL, function () {
            return AdministrativeArea::where('level', AdministrativeArea::LEVEL_REGENCIES)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])
                ->values()
                ->all();
        });
        $regencies = collect($regenciesData)->map(fn ($r) => (object) $r);

        $districtsData = Cache::remember('filter_districts_list', $cacheTTL, function () {
            return AdministrativeArea::where('level', AdministrativeArea::LEVEL_DISTRICTS)
                ->with('parent')
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id'])
                ->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'parent_id' => $d->parent_id,
                    'parent_name' => $d->parent?->name,
                ])
                ->values()
                ->all();
        });
        $districts = collect($districtsData)->map(fn ($d) => (object) $d);

        $villagesData = Cache::remember('filter_villages_list', $cacheTTL, function () {
            return AdministrativeArea::where('level', AdministrativeArea::LEVEL_VILLAGES)
                ->with('parent')
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id'])
                ->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'parent_id' => $v->parent_id,
                    'parent_name' => $v->parent?->name,
                ])
                ->values()
                ->all();
        });
        $villages = collect($villagesData)->map(fn ($v) => (object) $v);

        $provinceId = Cache::remember('province_first_id', $cacheTTL, function () {
            return AdministrativeArea::where('level', AdministrativeArea::LEVEL_PROVINCE)->value('id');
        });

        return view('livewire.public.statistik', [
            'pageBackground' => Setting::get('statistik_background_image'),
            'years' => SettlementStatistics::availableYears(),
            'province' => $provinceId ? AdministrativeArea::find($provinceId) : null,
            'regencies' => $regencies,
            'regenciesJson' => $regencies->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
            ])->values(),
            'districtsJson' => $districts->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'parent_id' => $d->parent_id,
                'parent' => $d->parent_name,
            ])->values(),
            'villagesJson' => $villages->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'parent_id' => $v->parent_id,
                'parent' => $v->parent_name,
            ])->values(),
        ]);
    }
}
