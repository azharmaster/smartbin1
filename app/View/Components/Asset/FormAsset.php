<?php

namespace App\View\Components\Asset;

use Closure;
use App\Models\Asset;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormAsset extends Component
{
    /**
     * Create a new component instance.
     */
    public $id, $asset_name, $floor_id, $serialNo, $location, $model, $maintenance;

    public function __construct($id = null)
    {
        if($id) {
            $assets = Asset::find($id);
            $this->id = $assets->id;
            $this->asset_name = $assets->asset_name;
            $this->floor_id = $assets->floor_id;
            $this->serialNo = $assets->serialNo;
            $this->location = $assets->location;
            $this->model = $assets->model;
            $this->maintenance = $assets->maintenance;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.asset.form-asset');
    }
}
