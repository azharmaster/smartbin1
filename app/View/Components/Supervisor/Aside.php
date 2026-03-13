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
                'label' => 'Dashboard',
                'icon'  => 'fas fa-tachometer-alt',
                'color' => '#4e73df',
                'is_dropdown' => true,
                'dropdown' => [
                    [
                        'label' => 'Dashboard',
                        "icon" => "fas fa-chart-line",
                        "color" => "#4e73df",
                        'route_name' => 'supervisor.dashboard',
                        'route_active' => 'supervisor.dashboard',
                    ],
                    [
                        'label' => 'Live Dashboard',
                        "icon" => "fas fa-chart-line",
                        "color" => "#1cc88a",
                        'route_name' => 'supervisor.main_dashboard',
                        'route_active' => 'supervisor.main_dashboard',
                    ],
                ],
                'is_logout' => false,
            ],
            
            // WorkForceHub (UNCHANGED)
            [
                "label" => "WorkForceHub",
                "icon" => "fas fa-briefcase",
                "color" => "#1cc88a",
                "route_active" => [
                    "users.*",
                    "floors.*",
                    "tasks.*",
                    "schedules.*"
                ],
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "Assign Tasks",
                        "icon" => "fas fa-tasks",
                        "color" => "#e74a3b",
                        "route_name" => "tasks.index",
                        "route_active" => "tasks.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Schedule",
                        "icon" => "fas fa-calendar-alt",
                        "color" => "#858796",
                        "route_name" => "schedules.index",
                        "route_active" => "schedules.*",
                        "is_dropdown" => false
                    ],
                ]
            ],

            // Collective (UNCHANGED)
            [
                "label" => "Collective",
                "icon" => "fas fa-database",
                "color" => "#6f42c1",
                "route_active" => "master-data.*",
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "Asset",
                        "route_active" => "master-data.assets.*",
                        "route_name" => "master-data.assets.index",
                        "icon" => "fas fa-circle",
                        "color" => "#4e73df",
                    ],
                    [
                        "label" => "Devices",
                        "route_active" => "devices.*",
                        "route_name" => "devices.index",
                        "icon" => "fas fa-circle",
                        "color" => "#1cc88a",
                    ],
                    [
                        "label" => "Sensor",
                        "route_active" => "sensors.*",
                        "route_name" => "sensors.index",
                        "icon" => "fas fa-circle",
                        "color" => "#f6c23e",
                    ],
                    [
                        "label" => "KPI BIN",
                        "route_active" => "kpi.bin.*",
                        "route_name" => "kpi.bin.index",
                        "icon" => "fas fa-circle",
                        "color" => "#e74a3b",
                    ],
                    [
                        "label" => "KPI SENSOR",
                        "route_active" => "kpi.sensor.*",
                        "route_name" => "kpi.sensor.index",
                        "icon" => "fas fa-circle",
                        "color" => "#f6c23e",
                    ],
                    [
                        "label" => "Complaints",
                        "route_active" => "complaints.*",
                        "route_name" => "complaints.index",
                        "icon" => "fas fa-circle",
                        "color" => "#e74a3b",
                    ],
                ]
            ],
            [
                "label" => "Logout",
                "icon" => "fas fa-sign-out-alt",
                "color" => "#e74a3b",
                "route_name" => "logout",
                "route_active" => null,
                "is_dropdown" => false,
                "is_logout" => true
            ],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.supervisor.aside');
    }
}
