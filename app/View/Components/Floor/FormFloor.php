<?php

namespace App\View\Components\Floor;

use App\Models\Floor;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormFloor extends Component
{
    /**
     * Create a new component instance.
     */
    public $id, $floor_name, $picture;
    public function __construct($id = null)
    {
        if($id) {
            $floors = Floor::find($id);
            $this->id = $floors->id;
            $this->floor_name = $floors->floor_name;
            $this->picture = $floors->picture;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.floor.form-floor');
    }
}
