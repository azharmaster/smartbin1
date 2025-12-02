<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->orderBy('date', 'desc')->get();
        return view('attendance.index', compact('attendances'));
    }
}
