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
    public $id, $asset_name, $floor_id, $serialNo, $location, $model, $latitude, $longitude, $is_active;

    public function __construct($id = null)
    {
        $this->is_active = 1;

        if($id) {
            $assets = Asset::find($id);
            $this->id = $assets->id;
            $this->asset_name = $assets->asset_name;
            $this->floor_id = $assets->floor_id;
            $this->serialNo = $assets->serialNo;
            $this->location = $assets->location;
            $this->model = $assets->model;
            $this->latitude = $assets->latitude;
            $this->longitude = $assets->longitude;
            $this->is_active = (int) $assets->is_active;
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
