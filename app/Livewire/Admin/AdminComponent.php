<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Basis seluruh halaman admin. Menyediakan state sidebar (mobile)
 * dan aksi logout yang dipakai layout admin via @entangle/wire:click.
 */
abstract class AdminComponent extends Component
{
    /** Status sidebar mobile (open by default di desktop via CSS lg:translate-x-0). */
    public bool $sidebarOpen = false;

    /** Judul halaman yang ditampilkan di topbar (di-set oleh subclass). */
    public string $pageTitle = '';

    /** @return array<int, array{label: string, url?: string}> */
    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ];
    }

    /** Computed accessor untuk breadcrumb (dipakai di layout). */
    public function getBreadcrumbsProperty(): array
    {
        return $this->breadcrumbsProperty();
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('admin.login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.placeholder')
            ->layoutData([
                'pageTitle' => $this->pageTitle,
                'breadcrumbs' => $this->breadcrumbs,
            ]);
    }
}
