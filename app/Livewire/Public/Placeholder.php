<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Komponen placeholder untuk halaman publik yang belum dibangun.
 *
 * Digunakan sementara oleh rute seperti /peta, /statistik,
 * /tentang, /metodologi, /tim, /aktivitas, dan /faq sehingga tautan pada
 * navbar & footer tidak rusak. Setiap halaman nantinya dapat diganti dengan
 * komponen Livewire khusus tanpa mengubah struktur layout publik.
 */
#[Layout('layouts.public')]
class Placeholder extends Component
{
    public function render()
    {
        $titles = [
            'peta' => 'Peta Interaktif',
            'statistik' => 'Statistik',
            'tentang' => 'Tentang',
            'metodologi' => 'Metodologi',
            'tim' => 'Tim',
            'aktivitas' => 'Aktivitas',
            'faq' => 'FAQ',
        ];

        $routeName = request()->route()?->getName();

        return view('livewire.public.placeholder', [
            'title' => $titles[$routeName] ?? 'Halaman',
        ]);
    }
}
