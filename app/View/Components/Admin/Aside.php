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

            //DASHBOARD
            [
                "type" => "section",
                "label" => "Dashboard"
            ],
            [
                "label" => "Main Dashboard",
                "icon" => "fas fa-chart-line",
                "color" => "#4e73df",
                "route_name" => "dashboard",
                "route_active" => "dashboard"
            ],
            [
                "label" => "Summary",
                "icon" => "fas fa-chart-pie",
                "color" => "#36b9cc",
                "route_name" => "summary",
                "route_active" => "summary"
            ],

            // WORKFORCEHUB
            [
                "type" => "section",
                "label" => "WorkForceHub"
            ],
            [
                "label" => "Users",
                "icon" => "fas fa-users",
                "color" => "#36b9cc",
                "route_name" => "users.index",
                "route_active" => "users.*"
            ],
            [
                "label" => "Holiday and Event",
                "icon" => "fas fa-calendar-alt",
                "color" => "#f6c23e",
                "route_name" => "holidays.index",
                "route_active" => "holidays.*"
            ],
            // [
            //     "label" => "Event",
            //     "icon" => "fas fa-calendar-alt",
            //     "color" => "#4e73df",
            //     "route_name" => "events.index",
            //     "route_active" => "events.*"
            // ],
            
            // SETTINGS
            [
                "type" => "section",
                "label" => "Settings"
            ],
            [
                "label" => "Notifications",
                "icon" => "fas fa-bell",
                "color" => "#f6c23e",
                "route_name" => "whatsapp.index",
                "route_active" => "whatsapp.*"
            ],
            [
                "label" => "Set Capacity",
                "icon" => "fas fa-sliders-h",
                "color" => "#36b9cc",
                "route_name" => "capacity.index",
                "route_active" => "capacity.*"
            ],

            // ASSETHUB
            [
                "label" => "Floor",
                "icon" => "fas fa-building",
                "color" => "#3ef0f6",
                "route_name" => "floors.index",
                "route_active" => "floors.*"
            ],
            [
                "type" => "section",
                "label" => "AssetHub"
            ],
            [
                "label" => "Assets",
                "icon" => "fas fa-database",
                "color" => "#8061ba",
                "route_name" => "master-data.assets.index",
                "route_active" => "master-data.assets.*"
            ],
            [
                "label" => "Devices",
                "icon" => "fas fa-microchip",
                "color" => "#1cc88a",
                "route_name" => "devices.index",
                "route_active" => "devices.*"
            ],
            [
                "label" => "Sensors",
                "icon" => "fas fa-broadcast-tower",
                "color" => "#6fff00",
                "route_name" => "sensors.index",
                "route_active" => "sensors.*"
            ],

            /* ✅ NEW: QR Scanner */
            [
                "label" => "QR Scanner",
                "icon" => "fas fa-qrcode",
                "color" => "#f59e0b",
                "route_name" => "qr.scanner",
                "route_active" => "qr.scanner"
            ],

            // LOGOUT
            [
                "type" => "divider"
            ],
            [
                "label" => "Logout",
                "icon" => "fas fa-sign-out-alt",
                "color" => "#e16256",
                "route_name" => "logout",
                "is_logout" => true
            ],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.aside');
    }
}