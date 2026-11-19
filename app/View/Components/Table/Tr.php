<?php

namespace App\View\Components\Table;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Tr extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?Model $item = null) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table.tr');
    }
}
