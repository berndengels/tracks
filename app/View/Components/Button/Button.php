<?php

namespace App\View\Components\Button;

use Illuminate\View\Component;

abstract class Button extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?string $route = null,
        public ?string $class = null
    )
    {}
}
