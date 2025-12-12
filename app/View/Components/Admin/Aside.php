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
                "label" => "Dashboard",
                "icon" => "fa-solid fas fa-tachometer-alt",
                "route_name" => "dashboard",
                "route_active" => "dashboard",
                "is_dropdown" => false
            ],
            [
                "label" => "WorkForceHub",
                "icon" => "fas fa-briefcase",
                "route_active" => [
                    "users.*",
                    "floors.*",
                    "tasks.*",
                    "schedules.*"
                ],
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "User",
                        "icon" => "fa-solid fas fa-users",
                        "route_name" => "users.index",
                        "route_active" => "users.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Floor",
                        "icon" => "fa-solid fas fa-building",
                        "route_name" => "floors.index",
                        "route_active" => "floors.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Assign Tasks",
                        "icon" => "fas fa-tasks",
                        "route_name" => "tasks.index",
                        "route_active" => "tasks.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Schedule",
                        "icon" => "fas fa-calendar-alt",
                        "route_name" => "schedules.index",
                        "route_active" => "schedules.*",
                        "is_dropdown" => false
                    ],
                ]
            ],
            [
                "label" => "Leave",
                "icon" => "fas fa-calendar-alt",
                "route_active" => "staff.leave.*",
                "is_dropdown" => false,
                "route_name" => "admin.leave.index", // Updated to a valid route
            ],
            [
                "label" => "Collective",
                "icon" => "fas fa-database",
                "route_active" => "master-data.*",
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "Asset",
                        "route_active" => "master-data.assets.*",
                        "route_name" => "master-data.assets.index",
                        "icon" => "fas fa-circle"
                    ],
                    [
                        "label" => "Devices",
                        "route_active" => "devices.*",
                        "route_name" => "devices.index",
                        "icon" => "fas fa-circle"
                    ],
                    [
                        "label" => "Sensor",
                        "route_active" => "sensors.*",
                        "route_name" => "sensors.index",
                        "icon" => "fas fa-circle"
                    ],
                    [
                        "label" => "Complaints",
                        "route_active" => "complaints.*",
                        "route_name" => "complaints.index",
                        "icon" => "fas fa-circle"
                    ],
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
