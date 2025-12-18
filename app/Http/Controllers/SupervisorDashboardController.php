<?php

namespace App\Http\Controllers;

use App\Models\User;

class SupervisorDashboardController extends Controller
{
    /**
     * Display the supervisor dashboard (only staff users).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get only users with role 2 (Staff)
        $staffUsers = User::where('role', 2)->get();

        // Pass staff users to the view
        return view('dashboard.supervisorindex', compact('staffUsers'));
    }
}
