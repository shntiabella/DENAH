<?php

namespace App\Livewire\Public;

use App\Models\AboutContent;
use App\Models\HeroContent;
use App\Models\MethodologyContent;
use App\Models\MethodologyStep;
use App\Support\SettlementStatistics;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('DENAH — Jelajahi Persebaran Permukiman')]
class Home extends Component
{
    /**
     * Data yang ditampilkan pada section "Data dalam Angka".
     *
     * Nilai dihitung langsung dari tabel settlement_areas (database) sehingga
     * berubah otomatis mengikuti data terbaru. Key "counter" menandai angka
     * yang dianimasikan naik saat masuk viewport.
     *
     * @return array<int, array{label: string, value: int, unit: string, icon: string, hint: string, counter: bool}>
     */
    public function stats(): array
    {
        return Cache::remember('home_stats', 86400, function () {
            $query = SettlementStatistics::query();
            $totals = SettlementStatistics::totals(clone $query);
            $years = SettlementStatistics::availableYears();

            return [
                [
                    'label' => __('Luas Permukiman'),
                    'value' => (int) round($totals['total_area_ha']),
                    'unit' => __('Ha'),
                    'icon' => 'area',
                    'hint' => __('Total kawasan terpetakan'),
                    'counter' => true,
                ],
                [
                    'label' => __('Wilayah Terpetakan'),
                    'value' => (int) $totals['total_records'],
                    'unit' => __('wilayah'),
                    'icon' => 'pins',
                    'hint' => __('Cakupan area pemetaan'),
                    'counter' => true,
                ],
                [
                    'label' => __('Data Spasial'),
                    'value' => $years->isNotEmpty() ? $years->count() : 0,
                    'unit' => __('dataset'),
                    'icon' => 'layers',
                    'hint' => __('Tercakup dalam sistem'),
                    'counter' => true,
                ],
                [
                    'label' => __('Tahun Pemetaan'),
                    'value' => $years->isNotEmpty() ? (int) $years->max() : (int) date('Y'),
                    'unit' => '',
                    'icon' => 'calendar',
                    'hint' => __('Periode pemetaan terkini'),
                    'counter' => false,
                ],
            ];
        });
    }

    public function render()
    {
        $methodologies = MethodologyStep::orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'title', 'title_id', 'title_en', 'description', 'description_id', 'description_en', 'icon', 'sort_order']);

        return view('livewire.public.home', [
            'stats' => $this->stats(),
            'about' => AboutContent::current(),
            'hero' => HeroContent::current(),
            'methodology' => MethodologyContent::current(),
            'methodologies' => $methodologies,
        ]);
    }
}
