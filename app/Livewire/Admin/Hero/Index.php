<?php

namespace App\Livewire\Admin\Hero;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\HeroBadge;
use App\Models\HeroContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Kelola Hero — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;
    use WithFileUploads;

    public string $pageTitle = 'Kelola Halaman Beranda (Hero)';

    public string $badgeTextId = '';
    public string $badgeTextEn = '';
    public string $headline1Id = '';
    public string $headline1En = '';
    public string $headline2Id = '';
    public string $headline2En = '';
    public string $subheadlineId = '';
    public string $subheadlineEn = '';
    public string $ctaPrimaryTextId = '';
    public string $ctaPrimaryTextEn = '';
    public string $ctaSecondaryTextId = '';
    public string $ctaSecondaryTextEn = '';
    public string $coordinates = '';
    public $backgroundImage;
    public string $backgroundImagePick = '';

    // Badges
    public string $newBadge = '';
    public ?int $editingBadgeId = null;
    public string $editingBadgeLabel = '';

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Halaman Beranda'],
        ];
    }

    public function mount(): void
    {
        $hero = HeroContent::current();
        $this->fill([
            'badgeTextId' => $hero->badge_text_id ?? $hero->badge_text ?? '',
            'badgeTextEn' => $hero->badge_text_en ?? '',
            'headline1Id' => $hero->headline_1_id ?? $hero->headline_1 ?? '',
            'headline1En' => $hero->headline_1_en ?? '',
            'headline2Id' => $hero->headline_2_id ?? $hero->headline_2 ?? '',
            'headline2En' => $hero->headline_2_en ?? '',
            'subheadlineId' => $hero->subheadline_id ?? $hero->subheadline ?? '',
            'subheadlineEn' => $hero->subheadline_en ?? '',
            'ctaPrimaryTextId' => $hero->cta_primary_text_id ?? $hero->cta_primary_text ?? '',
            'ctaPrimaryTextEn' => $hero->cta_primary_text_en ?? '',
            'ctaSecondaryTextId' => $hero->cta_secondary_text_id ?? $hero->cta_secondary_text ?? '',
            'ctaSecondaryTextEn' => $hero->cta_secondary_text_en ?? '',
            'coordinates' => $hero->coordinates ?? '',
        ]);
    }

    public function saveHero(): void
    {
        $validated = $this->validate([
            'badgeTextId' => 'required|string|max:120',
            'badgeTextEn' => 'nullable|string|max:120',
            'headline1Id' => 'required|string|max:120',
            'headline1En' => 'nullable|string|max:120',
            'headline2Id' => 'required|string|max:120',
            'headline2En' => 'nullable|string|max:120',
            'subheadlineId' => 'nullable|string|max:400',
            'subheadlineEn' => 'nullable|string|max:400',
            'ctaPrimaryTextId' => 'required|string|max:60',
            'ctaPrimaryTextEn' => 'nullable|string|max:60',
            'ctaSecondaryTextId' => 'required|string|max:60',
            'ctaSecondaryTextEn' => 'nullable|string|max:60',
            'coordinates' => 'nullable|string|max:60',
            'backgroundImage' => 'nullable|image|max:4096',
        ]);

        $hero = HeroContent::current();
        $data = [
            'badge_text' => $validated['badgeTextId'],
            'badge_text_id' => $validated['badgeTextId'],
            'badge_text_en' => $validated['badgeTextEn'],
            'headline_1' => $validated['headline1Id'],
            'headline_1_id' => $validated['headline1Id'],
            'headline_1_en' => $validated['headline1En'],
            'headline_2' => $validated['headline2Id'],
            'headline_2_id' => $validated['headline2Id'],
            'headline_2_en' => $validated['headline2En'],
            'subheadline' => $validated['subheadlineId'],
            'subheadline_id' => $validated['subheadlineId'],
            'subheadline_en' => $validated['subheadlineEn'],
            'cta_primary_text' => $validated['ctaPrimaryTextId'],
            'cta_primary_text_id' => $validated['ctaPrimaryTextId'],
            'cta_primary_text_en' => $validated['ctaPrimaryTextEn'],
            'cta_secondary_text' => $validated['ctaSecondaryTextId'],
            'cta_secondary_text_id' => $validated['ctaSecondaryTextId'],
            'cta_secondary_text_en' => $validated['ctaSecondaryTextEn'],
            'coordinates' => $validated['coordinates'],
            'updated_by' => auth()->id(),
        ];

        if ($this->backgroundImage) {
            $path = $this->backgroundImage->store('hero', 'public');
            $data['background_image'] = $path;
        } elseif ($this->backgroundImagePick !== '') {
            $data['background_image'] = $this->backgroundImagePick;
        }

        $hero->update($data);
        ActivityLog::record('Hero', 'update', 'Memperbarui konten hero beranda');
        $this->dispatch('notify', type: 'success', message: 'Konten hero berhasil disimpan.');
        $this->reset('backgroundImage', 'backgroundImagePick');
    }

    #[On('media-selected')]
    public function setMediaPath(string $target, string $path): void
    {
        if ($target === 'backgroundImagePick') {
            $this->backgroundImagePick = $path;
        }
    }

    public function addBadge(): void
    {
        $this->validate(['newBadge' => 'required|string|max:40']);
        HeroBadge::create([
            'label' => $this->newBadge,
            'sort_order' => HeroBadge::max('sort_order') + 1,
        ]);
        ActivityLog::record('Hero Badge', 'create', 'Menambah badge "'.$this->newBadge.'"');
        $this->reset('newBadge');
        $this->dispatch('notify', type: 'success', message: 'Badge ditambahkan.');
    }

    public function editBadge(int $id): void
    {
        $badge = HeroBadge::findOrFail($id);
        $this->editingBadgeId = $id;
        $this->editingBadgeLabel = $badge->label;
    }

    public function updateBadge(): void
    {
        $this->validate(['editingBadgeLabel' => 'required|string|max:40']);
        HeroBadge::findOrFail($this->editingBadgeId)->update(['label' => $this->editingBadgeLabel]);
        ActivityLog::record('Hero Badge', 'update', 'Memperbarui badge');
        $this->reset('editingBadgeId', 'editingBadgeLabel');
        $this->dispatch('notify', type: 'success', message: 'Badge diperbarui.');
    }

    public function cancelEditBadge(): void
    {
        $this->reset('editingBadgeId', 'editingBadgeLabel');
    }

    public function deleteBadge(int $id): void
    {
        HeroBadge::findOrFail($id)->delete();
        ActivityLog::record('Hero Badge', 'delete', 'Menghapus badge');
        $this->dispatch('notify', type: 'success', message: 'Badge dihapus.');
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'badge') {
            $this->deleteBadge($id);
        }
    }

    public function moveBadge(int $id, string $direction): void
    {
        $badge = HeroBadge::findOrFail($id);
        $swap = $direction === 'up'
            ? HeroBadge::where('sort_order', '<', $badge->sort_order)->orderByDesc('sort_order')->first()
            : HeroBadge::where('sort_order', '>', $badge->sort_order)->orderBy('sort_order')->first();
        if ($swap) {
            [$badge->sort_order, $swap->sort_order] = [$swap->sort_order, $badge->sort_order];
            $badge->save();
            $swap->save();
        }
    }

    public function removeBackground(): void
    {
        $hero = HeroContent::current();
        if ($hero->background_image && Storage::disk('public')->exists($hero->background_image)) {
            Storage::disk('public')->delete($hero->background_image);
        }
        $hero->update(['background_image' => null]);
        $this->dispatch('notify', type: 'success', message: 'Gambar background dihapus.');
    }

    public function render()
    {
        $hero = HeroContent::current();
        $badges = HeroBadge::orderBy('sort_order')->get();

        return view('livewire.admin.hero.index', [
            'hero' => $hero,
            'badges' => $badges,
        ]);
    }
}
