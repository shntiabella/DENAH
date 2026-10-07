<?php

namespace App\Console\Commands;

use App\Livewire\Admin\Hero\Index;
use Illuminate\Console\Command;

class DebugHeroRender extends Command
{
    protected $name = 'debug:hero';
    protected $description = 'Render hero component and show top-level structure';

    public function handle(): void
    {
        $component = app(Index::class);
        $component->mount();
        $html = $component->render()->render();

        // strip leading doctype/html wrappers if any (component view shouldn't have layout here)
        $this->info('Total length: '.strlen($html));
        $this->info('First 300 chars:');
        $this->line(substr($html, 0, 300));
        $this->info('Last 300 chars:');
        $this->line(substr($html, -300));
    }
}
