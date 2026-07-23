<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Map extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $bounds,
        public string $tracks,
        public string $points,
        public string $media,
        public ?int $duration = null
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.map');
    }
}
