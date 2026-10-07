<?php

namespace App\Livewire\Admin\Features;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\Feature;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Fitur Utama — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;

    public string $pageTitle = 'Kelola Fitur Utama';

    public ?int $editingId = null;
    public string $title = '';
    public string $description = '';
    public string $icon = 'map';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:80',
            'description' => 'nullable|string|max:300',
            'icon' => 'required|string|max:30',
        ];
    }

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Fitur Utama'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'description', 'icon']);
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $f = Feature::findOrFail($id);
        $this->editingId = $id;
        $this->title = $f->title;
        $this->description = $f->description ?? '';
        $this->icon = $f->icon;
    }

    public function save(): void
    {
        $this->validate();
        $data = ['title' => $this->title, 'description' => $this->description, 'icon' => $this->icon];

        if ($this->editingId) {
            Feature::findOrFail($this->editingId)->update($data);
            ActivityLog::record('Feature', 'update', 'Memperbarui fitur "'.$this->title.'"');
            $this->dispatch('notify', type: 'success', message: 'Fitur diperbarui.');
        } else {
            $data['sort_order'] = Feature::max('sort_order') + 1;
            Feature::create($data);
            ActivityLog::record('Feature', 'create', 'Menambah fitur "'.$this->title.'"');
            $this->dispatch('notify', type: 'success', message: 'Fitur ditambahkan.');
        }
        $this->resetForm();
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'feature') {
            Feature::findOrFail($id)->delete();
            ActivityLog::record('Feature', 'delete', 'Menghapus fitur');
            $this->dispatch('notify', type: 'success', message: 'Fitur dihapus.');
        }
    }

    public function move(int $id, string $direction): void
    {
        $item = Feature::findOrFail($id);
        $swap = $direction === 'up'
            ? Feature::where('sort_order', '<', $item->sort_order)->orderByDesc('sort_order')->first()
            : Feature::where('sort_order', '>', $item->sort_order)->orderBy('sort_order')->first();
        if ($swap) {
            [$item->sort_order, $swap->sort_order] = [$swap->sort_order, $item->sort_order];
            $item->save();
            $swap->save();
        }
    }

    public function render()
    {
        return view('livewire.admin.features.index', [
            'features' => Feature::orderBy('sort_order')->get(),
            'icons' => ['map' => 'Peta', 'chart' => 'Grafik', 'download' => 'Unduh', 'layers' => 'Layer', 'area' => 'Luas', 'pins' => 'Pin', 'calendar' => 'Kalender'],
        ]);
    }
}
