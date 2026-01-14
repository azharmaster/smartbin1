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

            // ✅ DASHBOARD WITH DROPDOWN
            [
                "label" => "Dashboard",
                "icon" => "fa-solid fas fa-tachometer-alt",
                "color" => "#98b2ffff", // blue
                "route_active" => [
                    "dashboard",
                    "dashboard.map",
                    "dashboard.report",
                    "summary"
                ],
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "Main Dashboard",
                        "icon" => "fas fa-chart-line",
                        "color" => "#4e73df",
                        "route_name" => "dashboard",
                        "route_active" => "dashboard",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Live Dashboard",
                        "icon" => "fas fa-chart-line",
                        "color" => "#1cc88a",
                        "route_name" => "admin.main.dashboard",
                        "route_active" => "admin.main.dashboard",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Summary",
                        "icon" => "fas fa-chart-pie",
                        "color" => "#36b9cc",
                        "route_name" => "summary",
                        "route_active" => "summary",
                        "is_dropdown" => false
                    ],
                ]
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
                    "whatsapp.*",
                    "capacity.*",
                    "holidays.*",
                    "events.*",
                    "schedules.*"
                ],
                "is_dropdown" => true,
                "dropdown" => [
                    [
                        "label" => "User",
                        "icon" => "fa-solid fas fa-users",
                        "color" => "#36b9cc",
                        "route_name" => "users.index",
                        "route_active" => "users.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Notification",
                        "icon" => "fas fa-bell",
                        "color" => "#f6c23e",
                        "route_name" => "whatsapp.index",
                        "route_active" => "whatsapp.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Set Capacity",
                        "icon" => "fas fa-sliders-h",
                        "color" => "#36b9cc",
                        "route_name" => "capacity.index",
                        "route_active" => "capacity.*",
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Holiday",
                        "icon" => "fa-solid fas fa-building",
                        "color" => "#f6c23e",
                        "route_name" => "holidays.index",
                        "route_active" => "holidays.*",
                        "is_dropdown" => false
                    ],
<<<<<<< Updated upstream
                    // [
                    //     "label" => "Floor",
                    //     "icon" => "fa-solid fas fa-building",
                    //     "color" => "#5f80d3ff",
                    //     "route_name" => "floors.index",
                    //     "route_active" => "floors.*",
                    //     "is_dropdown" => false
                    // ],
=======
                    [
                        "label" => "Event",
                        "icon" => "fa-solid fas fa-calendar-alt",
                        "color" => "#4e73df", // optional: choose a different color for calendar
                        "route_name" => "events.index", // change to your new events page
                        "route_active" => "events.*",   // highlights active menu items for all event routes
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Event",
                        "icon" => "fa-solid fas fa-calendar-alt",
                        "color" => "#4e73df", // optional: choose a different color for calendar
                        "route_name" => "events.index", // change to your new events page
                        "route_active" => "events.*",   // highlights active menu items for all event routes
                        "is_dropdown" => false
                    ],
                    [
                        "label" => "Floor",
                        "icon" => "fa-solid fas fa-building",
                        "color" => "#5f80d3ff",
                        "route_name" => "floors.index",
                        "route_active" => "floors.*",
                        "is_dropdown" => false
                    ],
>>>>>>> Stashed changes
                    // [
                    //     "label" => "Assign Tasks",
                    //     "icon" => "fas fa-tasks",
                    //     "color" => "#e74a3b",
                    //     "route_name" => "tasks.index",
                    //     "route_active" => "tasks.*",
                    //     "is_dropdown" => false
                    // ],
                    // [
                    //     "label" => "Schedule",
                    //     "icon" => "fas fa-calendar-alt",
                    //     "color" => "#858796",
                    //     "route_name" => "schedules.index",
                    //     "route_active" => "schedules.*",
                    //     "is_dropdown" => false
                    // ],

                    // --- COLLECTIVE (NESTED DROPDOWN) ---
                    [
                        "label" => "AssetHub",
                        "icon" => "fas fa-database",
                        "color" => "#6f42c1",
                        "route_active" => [
                            "master-data.*",
                            "devices.*"
                        ],
                        "is_dropdown" => true,
                        "dropdown" => [
                            [
                                "label" => "Asset",
                                "icon" => "fas fa-circle",
                                "color" => "#4e73df",
                                "route_name" => "master-data.assets.index",
                                "route_active" => "master-data.assets.*",
                                "is_dropdown" => false
                            ],
                            [
                                "label" => "Devices",
                                "icon" => "fas fa-circle",
                                "color" => "#1cc88a",
                                "route_name" => "devices.index",
                                "route_active" => "devices.*",
                                "is_dropdown" => false
                            ],
                        ]
                    ],
                ]
            ],

            // Leave (UNCHANGED)
            // [
            //     "label" => "Leave",
            //     "icon" => "fas fa-calendar-alt",
            //     "color" => "#fd7e14",
            //     "route_active" => "staff.leave.*",
            //     "is_dropdown" => false,
            //     "route_name" => "admin.leave.index",
            // ],

            // Collective (UNCHANGED)
    //         [
    // "label" => "Collective",
    // "icon" => "fas fa-database",
    // "color" => "#6f42c1",
    // "route_active" => [
    //     "master-data.*",
    //     "devices.*"
    // ],
    // "is_dropdown" => true,
    // "dropdown" => [
    //     [
    //         "label" => "Asset",
    //         "route_active" => "master-data.assets.*",
    //         "route_name" => "master-data.assets.index",
    //         "icon" => "fas fa-circle",
    //         "color" => "#4e73df",
    //     ],
    //     [
    //         "label" => "Devices",
    //         "route_active" => "devices.*",
    //         "route_name" => "devices.index",
    //         "icon" => "fas fa-circle",
    //         "color" => "#1cc88a",
    //     ],
                    // [
                    //     "label" => "Sensor",
                    //     "route_active" => "sensors.*",
                    //     "route_name" => "sensors.index",
                    //     "icon" => "fas fa-circle",
                    //     "color" => "#f6c23e",
                    // ],
                    // [
                    //     "label" => "Complaints",
                    //     "route_active" => "complaints.*",
                    //     "route_name" => "complaints.index",
                    //     "icon" => "fas fa-circle",
                    //     "color" => "#e74a3b",
                    // ],
            //     ]
            // ],

            // ✅ LOGOUT BUTTON
            [
                "label" => "Logout",
                "icon" => "fas fa-sign-out-alt",
                "color" => "#e74a3b",
                "route_name" => "logout", // Laravel logout route
                "route_active" => null,   // not active
                "is_dropdown" => false,
                "is_logout" => true       // special flag for blade
            ],

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
