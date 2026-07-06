<?php

namespace App\View\Components\Button;

use Closure;
use Illuminate\Contracts\View\View;
use function view;

class EditButton extends Button
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.button.edit-button');
    }
}
