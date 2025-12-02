<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;

class StaffScheduleController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Get schedules assigned to this staff
        $schedules = Schedule::with('floor')
            ->where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->get();

        return view('staff.schedules.index', compact('schedules'));
    }
}
