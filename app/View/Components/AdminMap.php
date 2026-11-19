<?php

namespace App\View\Components;

use Closure;
use App\Models\Track;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class AdminMap extends Component
{
    public $points;
    public array $bounds;
    /**
     * Create a new component instance.
     */
    public function __construct(
//        public array $bounds,
        public Track $track,
//        public string $points,
    ) {
        $this->points = json_encode($this->track->pointFeatures);
        $this->bounds = $track->bounds;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin-map');
    }
}
