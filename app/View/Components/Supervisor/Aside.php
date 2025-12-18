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
                'route_name' => 'supervisor.dashboard',
                'route_active' => 'supervisor.dashboard',
                'is_dropdown' => false,
                'is_logout' => false,
            ],
            // ✅ LOGOUT BUTTON
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
