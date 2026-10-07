<?php

namespace App\Livewire\Admin\Statistik;

use App\Livewire\Admin\AdminComponent;
use App\Models\AdministrativeArea;
use App\Support\SettlementStatistics;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Statistik — DENAH CMS')]
class Index extends AdminComponent
{
    public string $pageTitle = 'Statistik Data Permukiman';

    public ?int $year = null;

    public ?int $regencyId = null;

    public ?int $districtId = null;

    public ?int $villageId = null;

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Statistik'],
        ];
    }

    public function updatedRegencyId(): void
    {
        $this->districtId = null;
        $this->villageId = null;
    }

    public function updatedDistrictId(): void
    {
        $this->villageId = null;
    }

    public function render()
    {
        $regency = $this->regencyId ? AdministrativeArea::find($this->regencyId)?->name : null;
        $district = $this->districtId ? AdministrativeArea::find($this->districtId)?->name : null;
        $village = $this->villageId ? AdministrativeArea::find($this->villageId)?->name : null;

        $overview = SettlementStatistics::overview($this->year, $regency, $district, $village);

        return view('livewire.admin.statistik.index', [
            'overview' => $overview,
            'years' => SettlementStatistics::availableYears(),
            'regencies' => AdministrativeArea::where('level', AdministrativeArea::LEVEL_REGENCIES)->orderBy('name')->get(),
            'districts' => $this->regencyId
                ? AdministrativeArea::where('level', AdministrativeArea::LEVEL_DISTRICTS)->where('parent_id', $this->regencyId)->orderBy('name')->get()
                : collect(),
            'villages' => $this->districtId
                ? AdministrativeArea::where('level', AdministrativeArea::LEVEL_VILLAGES)->where('parent_id', $this->districtId)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
