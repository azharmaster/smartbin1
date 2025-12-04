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
                "label" => "Attendance",
                "icon" => "fas fa-user-check",
                "route_name" => "admin.attendance",
                "route_active" => "admin.attendance",
                "is_dropdown" => false
            ],
            [
                "label" => "Schedule",
                "icon" => "fas fa-calendar-alt",
                "route_name" => "schedules.index",
                "route_active" => "schedules.*",
                "is_dropdown" => false
            ],
            [
                "label" => "Leave",
                "icon" => "fas fa-home",
                "route_active" => "admin.leave.*",
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "Leave",
                        "icon" => "fas fa-calendar-alt",
                        "route_name" => "admin.leave.index",
                        "route_active" => "admin.leave.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Apply Leave",
                        "icon" => "fas fa-paper-plane",
                        "route_name" => "admin.leave.apply",
                        "route_active" => "admin.leave.apply",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Leave Quota",
                        "icon" => "fas fa-calendar-check",
                        "route_name" => "admin.leave.quota.index",
                        "route_active" => "admin.leave.quota.*",
                        "is_dropdown" => false
                    ],
                ]
            ],
            [
                "label" => "Master Data",
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
