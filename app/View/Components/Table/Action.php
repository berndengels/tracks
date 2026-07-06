<?php

namespace App\View\Components\Table;

use Closure;
use Illuminate\Support\Str;
use App\Libs\DataBinds\DataBinder;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Action extends Component
{
//    public Model $model;
    public $model;
    public string $paramName;
    public $hasActions = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?bool $edit = false,
        public ?bool $delete = false,
        public ?string $routePrefix = 'admin.'
    )
    {
        $this->model = app(DataBinder::class)->get();

        if($this->model && $this->model->count() > 0) {
            $classBasename = class_basename(get_class($this->model));
            $this->paramName = lcfirst(Str::singular($classBasename));

            if(auth()->check()) {
                $this->edit = true;
                $this->delete = true;
            }
        }

        if($this->edit && $this->delete) {
            $this->hasActions = true;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.table.action', [
            'model'         => $this->model,
            'modelParam'    => $this->paramName,
            'edit'          => $this->edit,
            'delete'        => $this->delete,
            'routePrefix'   => $this->routePrefix,
            'hasActions'    => $this->hasActions,
        ]);
    }
}
