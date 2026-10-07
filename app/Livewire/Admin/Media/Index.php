<?php

namespace App\Livewire\Admin\Media;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Media Library — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;
    use WithFileUploads;
    use WithPagination;

    public string $pageTitle = 'Media Library';

    public string $search = '';
    public string $filterType = 'all';
    public $upload;

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Media Library'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function uploadFile(): void
    {
        $this->validate(['upload' => 'required|file|max:20480']);
        $path = $this->upload->store('media', 'public');
        MediaFile::create([
            'name' => $this->upload->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $this->upload->getMimeType(),
            'size' => $this->upload->getSize(),
            'module' => 'media-library',
            'uploaded_by' => auth()->id(),
        ]);
        ActivityLog::record('Media', 'create', 'Mengunggah file media');
        $this->dispatch('notify', type: 'success', message: 'File diunggah.');
        $this->reset('upload');
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'media') {
            $m = MediaFile::findOrFail($id);
            if (Storage::disk($m->disk)->exists($m->path)) {
                Storage::disk($m->disk)->delete($m->path);
            }
            $m->delete();
            ActivityLog::record('Media', 'delete', 'Menghapus file media');
            $this->dispatch('notify', type: 'success', message: 'File dihapus.');
        }
    }

    public function render()
    {
        $media = MediaFile::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterType !== 'all', function ($q) {
                if ($this->filterType === 'image') {
                    $q->where('mime_type', 'like', 'image/%');
                } elseif ($this->filterType === 'geojson') {
                    $q->where(function ($q2) {
                        $q2->where('mime_type', 'like', 'application/%geo%')
                            ->orWhere('name', 'like', '%.geojson')
                            ->orWhere('name', 'like', '%.json');
                    });
                } elseif ($this->filterType === 'document') {
                    $q->where('mime_type', 'like', 'application/pdf')
                        ->orWhere('name', 'like', '%.csv')
                        ->orWhere('name', 'like', '%.pdf');
                }
            })
            ->latest()->paginate(12);

        return view('livewire.admin.media.index', ['media' => $media]);
    }
}
