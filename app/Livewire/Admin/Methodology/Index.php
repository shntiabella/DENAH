<?php

namespace App\Livewire\Admin\Methodology;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\MethodologyContent;
use App\Models\MethodologyStep;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Metodologi — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;
    use WithFileUploads;

    public string $pageTitle = 'Kelola Metodologi';

    // Step form
    public ?int $editingId = null;

    public string $titleId = '';

    public string $titleEn = '';

    public string $descriptionId = '';

    public string $descriptionEn = '';

    public string $icon = 'step';

    // Rich text content
    public string $bodyId = '';

    public string $bodyEn = '';

    // Judul section (CMS-driven, multi-bahasa)
    public string $contentTitleId = '';

    public string $contentTitleEn = '';

    // Background halaman
    public $backgroundImage;

    protected function rules(): array
    {
        return [
            'titleId' => 'required|string|max:80',
            'titleEn' => 'nullable|string|max:80',
            'descriptionId' => 'nullable|string|max:400',
            'descriptionEn' => 'nullable|string|max:400',
            'icon' => 'required|string|max:30',
            'bodyId' => 'nullable|string',
            'bodyEn' => 'nullable|string',
            'contentTitleId' => 'nullable|string|max:120',
            'contentTitleEn' => 'nullable|string|max:120',
        ];
    }

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Metodologi'],
        ];
    }

    public function mount(): void
    {
        $content = MethodologyContent::current();
        $this->contentTitleId = $content->title_id ?? $content->title ?? '';
        $this->contentTitleEn = $content->title_en ?? '';
        $this->bodyId = $content->body_id ?? $content->body ?? '';
        $this->bodyEn = $content->body_en ?? '';
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'titleId', 'titleEn', 'descriptionId', 'descriptionEn', 'icon']);
        $this->resetValidation();
    }

    public function editStep(int $id): void
    {
        $s = MethodologyStep::findOrFail($id);
        $this->editingId = $id;
        $this->titleId = $s->title_id ?? $s->title;
        $this->titleEn = $s->title_en ?? '';
        $this->descriptionId = $s->description_id ?? $s->description ?? '';
        $this->descriptionEn = $s->description_en ?? '';
        $this->icon = $s->icon;
    }

    public function saveStep(): void
    {
        $this->validate([
            'titleId' => 'required|string|max:80',
            'titleEn' => 'nullable|string|max:80',
            'descriptionId' => 'nullable|string|max:400',
            'descriptionEn' => 'nullable|string|max:400',
            'icon' => 'required|string|max:30',
        ]);
        $data = [
            'title' => $this->titleId,
            'title_id' => $this->titleId,
            'title_en' => $this->titleEn,
            'description' => $this->descriptionId,
            'description_id' => $this->descriptionId,
            'description_en' => $this->descriptionEn,
            'icon' => $this->icon,
        ];

        if ($this->editingId) {
            MethodologyStep::findOrFail($this->editingId)->update($data);
            ActivityLog::record('Methodology Step', 'update', 'Memperbarui step "'.$this->titleId.'"');
            $this->dispatch('notify', type: 'success', message: 'Step diperbarui.');
        } else {
            $data['sort_order'] = MethodologyStep::max('sort_order') + 1;
            MethodologyStep::create($data);
            ActivityLog::record('Methodology Step', 'create', 'Menambah step "'.$this->titleId.'"');
            $this->dispatch('notify', type: 'success', message: 'Step ditambahkan.');
        }
        $this->resetForm();
    }

    public function saveBody(): void
    {
        $this->validate([
            'contentTitleId' => 'nullable|string|max:120',
            'contentTitleEn' => 'nullable|string|max:120',
            'bodyId' => 'nullable|string',
            'bodyEn' => 'nullable|string',
            'backgroundImage' => 'nullable|image|max:8192',
        ]);
        $content = MethodologyContent::current();
        $data = [
            'title' => HtmlSanitizer::clean($this->contentTitleId),
            'title_id' => HtmlSanitizer::clean($this->contentTitleId),
            'title_en' => HtmlSanitizer::clean($this->contentTitleEn),
            'body' => HtmlSanitizer::clean($this->bodyId),
            'body_id' => HtmlSanitizer::clean($this->bodyId),
            'body_en' => HtmlSanitizer::clean($this->bodyEn),
            'updated_by' => auth()->id(),
        ];
        if ($this->backgroundImage) {
            $data['background_image'] = $this->backgroundImage->store('backgrounds', 'public');
        }
        $content->update($data);
        ActivityLog::record('Methodology Content', 'update', 'Memperbarui penjelasan metodologi');
        $this->dispatch('notify', type: 'success', message: 'Penjelasan metodologi disimpan.');
        $this->reset('backgroundImage');
    }

    public function removeBackground(): void
    {
        $content = MethodologyContent::current();
        if ($content->background_image && Storage::disk('public')->exists($content->background_image)) {
            Storage::disk('public')->delete($content->background_image);
        }
        $content->update(['background_image' => null]);
        $this->dispatch('notify', type: 'success', message: 'Gambar background dihapus.');
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'step') {
            MethodologyStep::findOrFail($id)->delete();
            ActivityLog::record('Methodology Step', 'delete', 'Menghapus step');
            $this->dispatch('notify', type: 'success', message: 'Step dihapus.');
        }
    }

    public function move(int $id, string $direction): void
    {
        $item = MethodologyStep::findOrFail($id);
        $swap = $direction === 'up'
            ? MethodologyStep::where('sort_order', '<', $item->sort_order)->orderByDesc('sort_order')->first()
            : MethodologyStep::where('sort_order', '>', $item->sort_order)->orderBy('sort_order')->first();
        if ($swap) {
            [$item->sort_order, $swap->sort_order] = [$swap->sort_order, $item->sort_order];
            $item->save();
            $swap->save();
        }
    }

    public function render()
    {
        return view('livewire.admin.methodology.index', [
            'content' => MethodologyContent::current(),
            'steps' => MethodologyStep::orderBy('sort_order')->get(),
            'icons' => [
                'satellite' => 'Citra Satelit',
                'preprocess' => 'Pra-pemrosesan',
                'sample' => 'Penentuan Sampel',
                'forest' => 'Random Forest',
                'result' => 'Hasil Klasifikasi',
                'check' => 'Pemeriksaan',
                'export' => 'Ekspor GeoJSON',
                'database' => 'Database',
                'webgis' => 'WebGIS & Statistik',
                'step' => 'Langkah (default)',
                'map' => 'Peta',
                'layers' => 'Layer',
                'download' => 'Unduh',
                'area' => 'Luas',
                'pins' => 'Pin',
            ],
        ]);
    }
}
