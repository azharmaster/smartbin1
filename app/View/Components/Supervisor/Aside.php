<?php

namespace App\View\Components\Supervisor;

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
                "label" => "Home",
                "icon" => "fas fa-home",
                "color" => "#4e73df",
                "route_name" => "supervisor.home",
                "route_active" => "supervisor.home",
                "is_dropdown" => false
            ],
            [
                "label" => "Dashboard",
                "icon" => "fas fa-tachometer-alt",
                "color" => "#1cc88a",
                "route_name" => "supervisor.dashboard",
                "route_active" => "supervisor.dashboard",
                "is_dropdown" => false
            ],
            [
                "label" => "Monitoring",
                "icon" => "fas fa-database",
                "color" => "#36b9cc",
                "route_active" => [
                    "supervisor.assets.*",
                    "supervisor.devices.*",
                ],
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "Assets",
                        "icon" => "fas fa-circle",
                        "color" => "#4e73df",
                        "route_name" => "supervisor.assets.index",
                        "route_active" => "supervisor.assets.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Devices",
                        "icon" => "fas fa-circle",
                        "color" => "#1cc88a",
                        "route_name" => "supervisor.devices.index",
                        "route_active" => "supervisor.devices.*",
                        "is_dropdown" => false
                    ],
                ]
            ],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.supervisor.aside');
    }
}
