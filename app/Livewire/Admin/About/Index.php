<?php

namespace App\Livewire\Admin\About;

use App\Livewire\Admin\AdminComponent;
use App\Models\AboutContent;
use App\Models\ActivityLog;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Tentang DENAH — DENAH CMS')]
class Index extends AdminComponent
{
    use WithFileUploads;

    public string $pageTitle = 'Kelola Section Tentang DENAH';

    public string $bodyId = '';

    public string $bodyEn = '';

    public string $moreLinkTextId = 'Selengkapnya';

    public string $moreLinkTextEn = 'Read More';

    public string $moreLinkUrl = '#tentang';

    public $image;

    public $backgroundImage;

    protected function rules(): array
    {
        return [
            'bodyId' => 'nullable|string',
            'bodyEn' => 'nullable|string',
            'moreLinkTextId' => 'required|string|max:40',
            'moreLinkTextEn' => 'nullable|string|max:40',
            'moreLinkUrl' => 'required|string|max:200',
            'image' => 'nullable|image|max:4096',
            'backgroundImage' => 'nullable|image|max:8192',
        ];
    }

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Tentang DENAH'],
        ];
    }

    public function mount(): void
    {
        $about = AboutContent::current();
        $this->bodyId = $about->body_id ?? $about->body ?? '';
        $this->bodyEn = $about->body_en ?? '';
        $this->moreLinkTextId = $about->more_link_text_id ?? $about->more_link_text;
        $this->moreLinkTextEn = $about->more_link_text_en ?? '';
        $this->moreLinkUrl = $about->more_link_url;
    }

    public function save(): void
    {
        $this->validate();
        $about = AboutContent::current();
        $data = [
            'body' => HtmlSanitizer::clean($this->bodyId),
            'body_id' => HtmlSanitizer::clean($this->bodyId),
            'body_en' => HtmlSanitizer::clean($this->bodyEn),
            'more_link_text' => $this->moreLinkTextId,
            'more_link_text_id' => $this->moreLinkTextId,
            'more_link_text_en' => $this->moreLinkTextEn,
            'more_link_url' => $this->moreLinkUrl,
            'updated_by' => auth()->id(),
        ];
        if ($this->image) {
            $data['image'] = $this->image->store('about', 'public');
        }
        if ($this->backgroundImage) {
            $data['background_image'] = $this->backgroundImage->store('backgrounds', 'public');
        }
        $about->update($data);
        ActivityLog::record('About', 'update', 'Memperbarui section tentang DENAH');
        $this->dispatch('notify', type: 'success', message: 'Section tentang disimpan.');
        $this->reset('image', 'backgroundImage');
    }

    public function removeBackground(): void
    {
        $about = AboutContent::current();
        if ($about->background_image && Storage::disk('public')->exists($about->background_image)) {
            Storage::disk('public')->delete($about->background_image);
        }
        $about->update(['background_image' => null]);
        $this->dispatch('notify', type: 'success', message: 'Gambar background dihapus.');
    }

    public function removeImage(): void
    {
        $about = AboutContent::current();
        if ($about->image && Storage::disk('public')->exists($about->image)) {
            Storage::disk('public')->delete($about->image);
        }
        $about->update(['image' => null]);
        $this->dispatch('notify', type: 'success', message: 'Gambar dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.about.index', [
            'about' => AboutContent::current(),
        ]);
    }
}
