<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Map extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $tracks,
        public string $points,
        public array $bounds,
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
