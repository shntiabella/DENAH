<?php

namespace App\Livewire\Admin\Media;

use App\Models\ActivityLog;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * ModalPicker "Pilih dari Media Library / References" yang reusable.
 *
 * Dipasang sebagai komponen anak pada form Admin (Settings/Hero) dengan
 * sebuah atribut `target` berisi nama property pada komponen induk yang akan
 * menerima path file terpilih. Saat gambar dipilih/di-upload, komponen ini
 * men-dispatch event browser `media-selected` (payload: target, path, url, name).
 */
class MediaPicker extends Component
{
    use WithFileUploads;

    public string $target = 'logoPick';
    public bool $open = false;
    public string $tab = 'gallery';
    public $upload;
    public string $search = '';

    public function mount(string $target = 'logoPick'): void
    {
        $this->target = $target;
        $this->tab = 'gallery';
    }

    #[On('open-media-picker')]
    public function open(array $params = []): void
    {
        if (isset($params['target']) && $params['target'] !== $this->target) {
            return;
        }
        $this->open = true;
        $this->tab = 'gallery';
        $this->reset('upload', 'search');
    }

    public function close(): void
    {
        $this->open = false;
        $this->reset('upload', 'search');
        $this->tab = 'gallery';
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab === 'upload' ? 'upload' : 'gallery';
    }

    public function uploadAndSelect(): void
    {
        $this->validate(['upload' => 'required|image|max:20480']);

        $path = $this->upload->store('media', 'public');
        $mf = MediaFile::create([
            'name' => $this->upload->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $this->upload->getMimeType(),
            'size' => $this->upload->getSize(),
            'module' => 'reference',
            'uploaded_by' => auth()->id(),
        ]);

        ActivityLog::record('Media', 'create', 'Mengunggah gambar referensi dari Media Picker');

        $this->dispatch('media-selected', target: $this->target, path: $mf->path, url: Storage::disk('public')->url($mf->path), name: $mf->name);
        $this->close();
    }

    public function select(int $id): void
    {
        $m = MediaFile::findOrFail($id);
        $this->dispatch('media-selected', target: $this->target, path: $m->path, url: Storage::disk('public')->url($m->path), name: $m->name);
        $this->close();
    }

    public function updatedSearch(): void
    {
        $this->tab = 'gallery';
    }

    public function render()
    {
        $media = MediaFile::query()
            ->where('mime_type', 'like', 'image/%')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->limit(30)
            ->get();

        return view('livewire.admin.media.media-picker', ['media' => $media]);
    }
}