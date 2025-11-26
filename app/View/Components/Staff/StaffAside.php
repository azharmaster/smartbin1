<?php

namespace App\View\Components\Staff;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Closure;

class StaffAside extends Component
{
    public $routes;

    public function __construct()
    {
        $this->routes = [
            [
                "label" => "Dashboard",
                "icon" => "fa-solid fas fa-tachometer-alt",
                "route_name" => "staff.dashboard",
                "route_active" => "staff.dashboard",
                "is_dropdown" => false
            ],
            [
                "label" => "My Tasks",
                "icon" => "fas fa-tasks",
                "route_name" => "staff.tasks",
                "route_active" => "staff.tasks.*",
                "is_dropdown" => false
            ],
            // You can add more staff menu items here if needed
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.staff.staff-aside');
    }
}
