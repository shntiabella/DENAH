<?php

namespace App\Livewire\Admin\Stats;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\Stat;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Data dalam Angka — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;

    public string $pageTitle = 'Kelola Data dalam Angka';

    public ?int $editingId = null;
    public string $label = '';
    public string $value = '';
    public string $unit = '';
    public string $icon = 'area';
    public bool $counter = true;

    protected function rules(): array
    {
        return [
            'label' => 'required|string|max:60',
            'value' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'icon' => 'required|string|max:30',
            'counter' => 'boolean',
        ];
    }

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Data dalam Angka'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'label', 'value', 'unit', 'icon', 'counter']);
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $stat = Stat::findOrFail($id);
        $this->editingId = $id;
        $this->label = $stat->label;
        $this->value = (string) $stat->value;
        $this->unit = $stat->unit ?? '';
        $this->icon = $stat->icon;
        $this->counter = (bool) $stat->counter;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'label' => $this->label,
            'value' => (float) $this->value,
            'unit' => $this->unit,
            'icon' => $this->icon,
            'counter' => $this->counter,
        ];

        if ($this->editingId) {
            Stat::findOrFail($this->editingId)->update($data);
            ActivityLog::record('Stat', 'update', 'Memperbarui statistik "'.$this->label.'"');
            $this->dispatch('notify', type: 'success', message: 'Statistik diperbarui.');
        } else {
            $data['sort_order'] = Stat::max('sort_order') + 1;
            Stat::create($data);
            ActivityLog::record('Stat', 'create', 'Menambah statistik "'.$this->label.'"');
            $this->dispatch('notify', type: 'success', message: 'Statistik ditambahkan.');
        }
        $this->resetForm();
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'stat') {
            Stat::findOrFail($id)->delete();
            ActivityLog::record('Stat', 'delete', 'Menghapus statistik');
            $this->dispatch('notify', type: 'success', message: 'Statistik dihapus.');
        }
    }

    public function move(int $id, string $direction): void
    {
        $item = Stat::findOrFail($id);
        $swap = $direction === 'up'
            ? Stat::where('sort_order', '<', $item->sort_order)->orderByDesc('sort_order')->first()
            : Stat::where('sort_order', '>', $item->sort_order)->orderBy('sort_order')->first();
        if ($swap) {
            [$item->sort_order, $swap->sort_order] = [$swap->sort_order, $item->sort_order];
            $item->save();
            $swap->save();
        }
    }

    public function render()
    {
        $stats = Stat::orderBy('sort_order')->get();

        return view('livewire.admin.stats.index', [
            'stats' => $stats,
            'icons' => [
                'area' => 'Luas Area', 'pins' => 'Pin Lokasi', 'layers' => 'Layer Data',
                'calendar' => 'Kalender', 'chart' => 'Grafik', 'download' => 'Unduh', 'map' => 'Peta',
            ],
        ]);
    }
}
