<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Aside extends Component
{
    public $routes;
  public function __construct()
    {

        $this->routes = [
            [
                "label" => "dashboard",
                "icon" => "fa-solid fas fa-tachometer-alt",
                "route_name" => "dashboard",
                "route_active" => "dashboard",
                "is_dropdown" => false
            ],
            [
                "label" => "master data",
                "icon" => "fas fa-database",
                "route_active" => "master-data.*",
                "is_dropdown" => true,
                "dropdown" => [ 
                    "label" => "kategori",
                    "route_active" => "master-data.kategori.*",
                    "route_name" => "master-data.kategori.index",
                ]
            ]
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.aside');
    }
}
