<?php

namespace App\Livewire\Admin\Footer;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\SocialLink;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Footer & Kontak — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;

    public string $pageTitle = 'Kelola Footer & Kontak';

    public string $footerDescription = '';
    public string $contactEmail = '';
    public string $contactPhone = '';
    public string $contactAddress = '';

    public string $newPlatform = '';
    public string $newIcon = 'link';
    public string $newUrl = '';
    public ?int $editingSocialId = null;
    public string $editPlatform = '';
    public string $editIcon = 'link';
    public string $editUrl = '';

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Footer & Kontak'],
        ];
    }

    public function mount(): void
    {
        $this->footerDescription = (string) Setting::get('footer_description', '');
        $this->contactEmail = (string) Setting::get('contact_email', '');
        $this->contactPhone = (string) Setting::get('contact_phone', '');
        $this->contactAddress = (string) Setting::get('contact_address', '');
    }

    public function saveContact(): void
    {
        $this->validate([
            'footerDescription' => 'nullable|string|max:600',
            'contactEmail' => 'nullable|email|max:120',
            'contactPhone' => 'nullable|string|max:40',
            'contactAddress' => 'nullable|string|max:200',
        ]);
        Setting::set('footer_description', $this->footerDescription);
        Setting::set('contact_email', $this->contactEmail);
        Setting::set('contact_phone', $this->contactPhone);
        Setting::set('contact_address', $this->contactAddress);
        ActivityLog::record('Footer', 'update', 'Memperbarui footer & kontak');
        $this->dispatch('notify', type: 'success', message: 'Footer & kontak disimpan.');
    }

    public function addSocial(): void
    {
        $this->validate(['newPlatform' => 'required|string|max:40', 'newIcon' => 'required|string|max:30', 'newUrl' => 'required|url|max:200']);
        SocialLink::create([
            'platform' => $this->newPlatform, 'icon' => $this->newIcon, 'url' => $this->newUrl,
            'sort_order' => SocialLink::max('sort_order') + 1,
        ]);
        ActivityLog::record('Social Link', 'create', 'Menambah sosial media "'.$this->newPlatform.'"');
        $this->reset('newPlatform', 'newIcon', 'newUrl');
        $this->newIcon = 'link';
        $this->dispatch('notify', type: 'success', message: 'Sosial media ditambahkan.');
    }

    public function editSocial(int $id): void
    {
        $s = SocialLink::findOrFail($id);
        $this->editingSocialId = $id;
        $this->editPlatform = $s->platform;
        $this->editIcon = $s->icon;
        $this->editUrl = $s->url;
    }

    public function updateSocial(): void
    {
        $this->validate(['editPlatform' => 'required|string|max:40', 'editIcon' => 'required|string|max:30', 'editUrl' => 'required|url|max:200']);
        SocialLink::findOrFail($this->editingSocialId)->update([
            'platform' => $this->editPlatform, 'icon' => $this->editIcon, 'url' => $this->editUrl,
        ]);
        $this->reset('editingSocialId', 'editPlatform', 'editIcon', 'editUrl');
        $this->dispatch('notify', type: 'success', message: 'Sosial media diperbarui.');
    }

    public function cancelEditSocial(): void
    {
        $this->reset('editingSocialId', 'editPlatform', 'editIcon', 'editUrl');
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'social') {
            SocialLink::findOrFail($id)->delete();
            ActivityLog::record('Social Link', 'delete', 'Menghapus sosial media');
            $this->dispatch('notify', type: 'success', message: 'Sosial media dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.admin.footer.index', [
            'socials' => SocialLink::orderBy('sort_order')->get(),
        ]);
    }
}
