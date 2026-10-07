<?php

namespace App\Livewire\Admin\Areas;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\AdministrativeArea;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Wilayah — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;
    use WithPagination;

    public string $pageTitle = 'Kelola Wilayah Administrasi';

    public ?int $editingId = null;
    public string $name = '';
    public string $code = '';
    public string $level = 'kabupaten';
    public ?int $parentId = null;
    public ?string $lat = null;
    public ?string $lng = null;

    public string $search = '';
    public string $filterLevel = 'all';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20',
            'level' => 'required|in:provinsi,kabupaten,kecamatan,desa',
            'parentId' => 'nullable|exists:administrative_areas,id',
            'lat' => 'nullable|numeric|between:-11,6',
            'lng' => 'nullable|numeric|between:95,141',
        ];
    }

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Wilayah'],
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterLevel(): void { $this->resetPage(); }

    public function updatedLevel(): void
    {
        $this->parentId = null;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'code', 'level', 'parentId', 'lat', 'lng']);
        $this->level = 'kabupaten';
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $area = AdministrativeArea::findOrFail($id);
        $this->editingId = $id;
        $this->name = $area->name;
        $this->code = $area->code ?? '';
        $this->level = $area->level;
        $this->parentId = $area->parent_id;
        $this->lat = $area->lat !== null ? (string) $area->lat : null;
        $this->lng = $area->lng !== null ? (string) $area->lng : null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'level' => $this->level,
            'parent_id' => $this->parentId,
            'lat' => $this->lat !== null && $this->lat !== '' ? (float) $this->lat : null,
            'lng' => $this->lng !== null && $this->lng !== '' ? (float) $this->lng : null,
        ];

        if ($this->editingId) {
            AdministrativeArea::findOrFail($this->editingId)->update($data);
            ActivityLog::record('Wilayah', 'update', 'Memperbarui wilayah "'.$this->name.'"');
            $this->dispatch('notify', type: 'success', message: 'Wilayah diperbarui.');
        } else {
            AdministrativeArea::create($data);
            ActivityLog::record('Wilayah', 'create', 'Menambah wilayah "'.$this->name.'"');
            $this->dispatch('notify', type: 'success', message: 'Wilayah ditambahkan.');
        }
        $this->resetForm();
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'area') {
            $area = AdministrativeArea::findOrFail($id);
            if ($area->children()->exists()) {
                $this->dispatch('notify', type: 'error', message: 'Wilayah masih memiliki anak. Hapus anak terlebih dahulu.');

                return;
            }
            $area->delete();
            ActivityLog::record('Wilayah', 'delete', 'Menghapus wilayah "'.$area->name.'"');
            $this->dispatch('notify', type: 'success', message: 'Wilayah dihapus.');
        }
    }

    public function render()
    {
        $areas = AdministrativeArea::query()
            ->with('parent')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterLevel !== 'all', fn ($q) => $q->where('level', $this->filterLevel))
            ->orderBy('level')->orderBy('name')->paginate(15);

        $parents = AdministrativeArea::whereIn('level', ['provinsi', 'kabupaten', 'kecamatan'])
            ->orderBy('level')->orderBy('name')->get();

        return view('livewire.admin.areas.index', [
            'areas' => $areas,
            'parents' => $parents,
        ]);
    }
}
