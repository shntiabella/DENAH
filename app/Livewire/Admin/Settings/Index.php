<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\AdminComponent;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Pengaturan Umum — DENAH CMS')]
class Index extends AdminComponent
{
    use WithFileUploads;

    public string $pageTitle = 'Pengaturan Umum';

    public string $siteName = '';

    public string $tagline = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public $logo;

    public $favicon;

    /** Path gambar yang dipilih dari Media Library (untuk disimpan saat save). */
    public string $logoPick = '';

    public string $faviconPick = '';

    // Background halaman Peta & Statistik
    public $petaBackground;

    public string $petaBackgroundPick = '';

    public $statistikBackground;

    public string $statistikBackgroundPick = '';

    #[On('media-selected')]
    public function setMediaPath(string $target, string $path): void
    {
        if ($target === 'logoPick') {
            $this->logoPick = $path;
        } elseif ($target === 'faviconPick') {
            $this->faviconPick = $path;
        } elseif ($target === 'petaBackgroundPick') {
            $this->petaBackgroundPick = $path;
        } elseif ($target === 'statistikBackgroundPick') {
            $this->statistikBackgroundPick = $path;
        }
    }

    protected function rules(): array
    {
        return [
            'siteName' => 'required|string|max:120',
            'tagline' => 'nullable|string|max:200',
            'metaTitle' => 'nullable|string|max:200',
            'metaDescription' => 'nullable|string|max:400',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'petaBackground' => 'nullable|image|max:8192',
            'statistikBackground' => 'nullable|image|max:8192',
        ];
    }

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Pengaturan Umum'],
        ];
    }

    public function mount(): void
    {
        $this->siteName = (string) Setting::get('site_name', config('app.name', 'DENAH'));
        $this->tagline = (string) Setting::get('tagline', '');
        $this->metaTitle = (string) Setting::get('meta_title', '');
        $this->metaDescription = (string) Setting::get('meta_description', '');
    }

    public function save(): void
    {
        $this->validate();
        Setting::set('site_name', $this->siteName);
        Setting::set('tagline', $this->tagline);
        Setting::set('meta_title', $this->metaTitle);
        Setting::set('meta_description', $this->metaDescription);

        if ($this->logo) {
            $path = $this->logo->store('settings', 'public');
            Setting::set('logo', $path);
        } elseif ($this->logoPick !== '') {
            Setting::set('logo', $this->logoPick);
        }

        if ($this->favicon) {
            $path = $this->favicon->store('settings', 'public');
            Setting::set('favicon', $path);
        } elseif ($this->faviconPick !== '') {
            Setting::set('favicon', $this->faviconPick);
        }

        if ($this->petaBackground) {
            $path = $this->petaBackground->store('backgrounds', 'public');
            Setting::set('peta_background_image', $path);
        } elseif ($this->petaBackgroundPick !== '') {
            Setting::set('peta_background_image', $this->petaBackgroundPick);
        }

        if ($this->statistikBackground) {
            $path = $this->statistikBackground->store('backgrounds', 'public');
            Setting::set('statistik_background_image', $path);
        } elseif ($this->statistikBackgroundPick !== '') {
            Setting::set('statistik_background_image', $this->statistikBackgroundPick);
        }

        ActivityLog::record('Settings', 'update', 'Memperbarui pengaturan umum');
        $this->dispatch('notify', type: 'success', message: 'Pengaturan disimpan.');
        $this->reset('logo', 'favicon', 'logoPick', 'faviconPick', 'petaBackground', 'petaBackgroundPick', 'statistikBackground', 'statistikBackgroundPick');
    }

    public function removeLogo(): void
    {
        $p = Setting::get('logo');
        if ($p && Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
        Setting::set('logo', null);
        $this->dispatch('notify', type: 'success', message: 'Logo dihapus.');
    }

    public function removeFavicon(): void
    {
        $p = Setting::get('favicon');
        if ($p && Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
        Setting::set('favicon', null);
        $this->dispatch('notify', type: 'success', message: 'Favicon dihapus.');
    }

    public function removePetaBackground(): void
    {
        $p = Setting::get('peta_background_image');
        if ($p && Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
        Setting::set('peta_background_image', null);
        $this->dispatch('notify', type: 'success', message: 'Background Peta dihapus.');
    }

    public function removeStatistikBackground(): void
    {
        $p = Setting::get('statistik_background_image');
        if ($p && Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
        Setting::set('statistik_background_image', null);
        $this->dispatch('notify', type: 'success', message: 'Background Statistik dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.settings.index', [
            'logoPath' => Setting::get('logo'),
            'faviconPath' => Setting::get('favicon'),
            'petaBackgroundPath' => Setting::get('peta_background_image'),
            'statistikBackgroundPath' => Setting::get('statistik_background_image'),
        ]);
    }
}
