<?php

namespace App\Livewire\Public;

use App\Models\MethodologyContent;
use App\Models\MethodologyStep;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Metodologi — DENAH')]
class Metodologi extends Component
{
    public function render()
    {
        $steps = MethodologyStep::orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'title', 'title_id', 'title_en', 'description', 'description_id', 'description_en', 'icon', 'sort_order']);

        return view('livewire.public.metodologi', [
            'methodology' => MethodologyContent::current(),
            'steps' => $steps,
        ]);
    }
}