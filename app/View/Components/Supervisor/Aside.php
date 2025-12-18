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
            ],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.supervisor.aside');
    }
}
