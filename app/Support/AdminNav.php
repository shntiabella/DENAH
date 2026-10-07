<?php

namespace App\Support;

/**
 * Definisi menu navigasi sidebar admin.
 */
class AdminNav
{
    /** @return array<int, array{route: string, label: string, icon: string, match?: array<string>}> */
    public static function items(): array
    {
        $icon = static fn (string $path): string => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg>';

        return [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => $icon('M3 12l9-9 9 9M5 10v10h14V10'), 'match' => ['admin.dashboard']],
            ['route' => 'admin.settlement-areas.index', 'label' => 'Data Permukiman', 'icon' => $icon('M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z'), 'match' => ['admin.settlement-areas.index']],
            ['route' => 'admin.areas.index', 'label' => 'Wilayah', 'icon' => $icon('M3 10h18M7 15h4m-4 0v4m0-4l2-2m8 2h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'), 'match' => ['admin.areas.index']],
            ['route' => 'admin.statistik.index', 'label' => 'Statistik', 'icon' => $icon('M4 19h16M6 16v-5M10 16V7M14 16v-8M18 16V5'), 'match' => ['admin.statistik.index']],
            ['route' => 'admin.import.index', 'label' => 'Import Data', 'icon' => $icon('M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5M5 21h14'), 'match' => ['admin.import.index']],
            ['route' => 'admin.users.index', 'label' => 'User/Admin', 'icon' => $icon('M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-6 0M16 7a4 4 0 11-8 0 4 4 0 018 0z')],
            ['route' => 'admin.hero.index', 'label' => 'Halaman Beranda', 'icon' => $icon('M3 12l9-9 9 9M5 10v10h14V10'), 'match' => ['admin.hero.index']],
            ['route' => 'admin.stats.index', 'label' => 'Data dalam Angka', 'icon' => $icon('M4 19h16M6 16v-5M10 16V7M14 16v-8M18 16V5')],
            ['route' => 'admin.features.index', 'label' => 'Fitur Utama', 'icon' => $icon('M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.5 5L21 12l-5 2.5L13 20l-2.5-5L5 12l5-2.5z')],
            ['route' => 'admin.about.index', 'label' => 'Tentang DENAH', 'icon' => $icon('M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z')],
            ['route' => 'admin.methodology.index', 'label' => 'Metodologi', 'icon' => $icon('M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4')],
            ['route' => 'admin.footer.index', 'label' => 'Footer & Kontak', 'icon' => $icon('M3 12h18M3 6h18M3 18h18')],
            ['route' => 'admin.media.index', 'label' => 'Media Library', 'icon' => $icon('M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z')],
            ['route' => 'admin.settings.index', 'label' => 'Pengaturan Umum', 'icon' => $icon('M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.758.426 1.758 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.758-2.924 1.758-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.758-.426-1.758-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z')],
        ];
    }
}
