<?php

namespace App\Livewire\Public;

use App\Models\AboutContent;
use App\Models\AdministrativeArea;
use App\Support\SettlementStatistics;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Tentang — DENAH')]
class Tentang extends Component
{
    public function render()
    {
        $totals = SettlementStatistics::totals(SettlementStatistics::query());
        $regencies = AdministrativeArea::where('level', AdministrativeArea::LEVEL_REGENCIES)->count();

        return view('livewire.public.tentang', [
            'about' => AboutContent::current(),
            'regencies' => $regencies,
            'totals' => $totals,
        ]);
    }
}