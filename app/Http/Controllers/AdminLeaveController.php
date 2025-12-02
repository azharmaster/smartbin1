<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use App\Models\LeaveQuota;
use Illuminate\Http\Request;

class AdminLeaveController extends Controller
{
    /**
     * Display all leave requests with user info.
     */
    public function index()
    {
        $leaves = Leave::with('user')->latest()->get();
        return view('admin.leave.index', compact('leaves'));
    }

    /**
     * Display all users' leave quotas and the add quota modal.
     */
    public function indexQuota()
    {
        $quotas = LeaveQuota::with('user')->get(); // Load all quotas with user info
        $users = User::all(); // All users for the add quota dropdown
        return view('admin.leave.create_quota_index', compact('quotas', 'users'));
    }

    /**
     * Store or update leave quota for a user.
     */
    public function storeQuota(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|digits:4',
            'annual_leave' => 'nullable|integer|min:0',
            'mc' => 'nullable|integer|min:0',
            'hospitality' => 'nullable|integer|min:0',
            'emergency_leave' => 'nullable|integer|min:0',
        ]);

        LeaveQuota::updateOrCreate(
            ['user_id' => $request->user_id, 'year' => $request->year],
            [
                'annual_leave' => $request->annual_leave ?? 0,
                'mc' => $request->mc ?? 0,
                'hospitality' => $request->hospitality ?? 0,
                'emergency_leave' => $request->emergency_leave ?? 0,
                'used_days' => 0,
            ]
        );

        return redirect()->route('admin.leave.quota.index')->with('success', 'Leave quota set successfully.');
    }

    /**
     * Approve or reject a leave request.
     */
    public function updateStatus(Request $request, Leave $leave)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,Approved,Rejected',
        ]);

        $status = ucfirst(strtolower($request->status));
        $leave->update(['status' => $status]);

        if ($status === 'Approved') {
            // Calculate the number of leave days correctly
            if ($leave->type === 'halfday') {
                $daysUsed = 0.5;
            } else {
                $start = strtotime($leave->start_date);
                $end = strtotime($leave->end_date ?? $leave->start_date);
                $daysUsed = ($end - $start) / 86400 + 1;
            }

            // Get user's quota for the current year
            $quota = LeaveQuota::where('user_id', $leave->user_id)
                        ->where('year', date('Y'))
                        ->first();

            if ($quota) {
                // Use leave->use directly as column key in quota table
                $validLeaveTypes = ['annual_leave', 'mc', 'hospitality', 'emergency_leave'];
                $leaveKey = strtolower(str_replace(' ', '_', $leave->use));

                if (in_array($leaveKey, $validLeaveTypes)) {
                    $quota->$leaveKey = max(0, ($quota->$leaveKey ?? 0) - $daysUsed);
                    $quota->used_days = ($quota->used_days ?? 0) + $daysUsed;
                    $quota->save();
                }
            }
        }

        return back()->with('success', 'Leave status updated successfully.');
    }

    /**
     * Show a single leave request detail (optional modal view).
     */
    public function show(Leave $leave)
    {
        $leave->load('user');
        return view('admin.leave.show', compact('leave'));
    }
}
