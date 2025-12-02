<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\LeaveQuota;
use Illuminate\Support\Facades\Auth;

class StaffLeaveController extends Controller
{
    public function index() {
        $leaves = Leave::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        $year = date('Y');
        $quota = LeaveQuota::where('user_id', Auth::id())->where('year', $year)->first();

        if (!$quota) {
            $quota = new LeaveQuota([
                'mc' => 0,
                'annual_leave' => 0,
                'emergency_leave' => 0,
                'hospitality' => 0,
                'used_days' => 0,
            ]);
        }

        return view('staff.leaves.index', compact('leaves', 'quota'));
    }

    public function store(Request $request) {
        $request->validate([
            'type' => 'required|in:halfday,fullday',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leave_use' => 'required|in:mc,emergency_leave,annual_leave,hospitality',
            'reason' => 'required|string|max:255',
        ]);

        $user_id = Auth::id();
        $leaveUse = $request->leave_use;

        // Calculate number of leave days
        if ($request->type === 'halfday') {
            $days = 0.5;
        } elseif ($request->type === 'fullday' && $request->end_date) {
            $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400 + 1;
        } else {
            $days = 1;
        }

        // Get the leave quota for the current year
        $year = date('Y');
        $quota = LeaveQuota::where('user_id', $user_id)->where('year', $year)->first();

        if (!$quota) {
            return redirect()->back()->with('error', 'Leave quota not set for this year.');
        }

        // Check remaining days for the selected leave type
        $remaining = $quota->$leaveUse;
        if ($remaining < $days) {
            return redirect()->back()->with('error', 'You have insufficient leave quota for ' . ucfirst(str_replace('_', ' ', $leaveUse)) . '.');
        }

        // Create the leave without deducting quota yet
        Leave::create([
            'user_id' => $user_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'use' => $leaveUse,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave applied successfully!');
    }
}
