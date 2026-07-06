<?php

namespace App\View\Components\Button;

use Closure;
use Illuminate\Contracts\View\View;

class PrintButton extends Button
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $route,
        public ?string $target = '_blank',
    )
    {
        parent::__construct($this->route);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.button.print-button');
    }
}
