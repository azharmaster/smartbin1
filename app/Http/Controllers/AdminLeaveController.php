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
     * Display all users' leave quotas and the add/edit quota modal.
     */
    public function indexQuota()
    {
        $quotas = LeaveQuota::with('user')->get(); // Load all quotas with user info
        $users = User::all(); // All users for the add quota dropdown
        return view('admin.leave.create_quota_index', compact('quotas', 'users'));
    }

    /**
     * Store or update leave quota for a user (works for both add and edit via modal).
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
     * Update leave quota via modal.
     */
    public function updateQuota(Request $request, LeaveQuota $quota)
    {
        $request->validate([
            'year' => 'required|integer|digits:4',
            'annual_leave' => 'nullable|integer|min:0',
            'mc' => 'nullable|integer|min:0',
            'hospitality' => 'nullable|integer|min:0',
            'emergency_leave' => 'nullable|integer|min:0',
            'used_days' => 'nullable|numeric|min:0',
        ]);

        $quota->update([
            'year' => $request->year,
            'annual_leave' => $request->annual_leave ?? 0,
            'mc' => $request->mc ?? 0,
            'hospitality' => $request->hospitality ?? 0,
            'emergency_leave' => $request->emergency_leave ?? 0,
            'used_days' => $request->used_days ?? 0,
        ]);

        return redirect()->route('admin.leave.quota.index')->with('success', 'Leave quota updated successfully.');
    }

    /**
     * Delete leave quota.
     */
    public function destroyQuota(LeaveQuota $quota)
    {
        $quota->delete();
        return redirect()->route('admin.leave.quota.index')->with('success', 'Leave quota deleted successfully.');
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
                // Only deduct the specific leave type applied
                switch ($leave->use) {
                    case 'annual_leave':
                        $quota->annual_leave = max(0, $quota->annual_leave - $daysUsed);
                        break;
                    case 'mc':
                        $quota->mc = max(0, $quota->mc - $daysUsed);
                        break;
                    case 'hospitality':
                        $quota->hospitality = max(0, $quota->hospitality - $daysUsed);
                        break;
                    case 'emergency_leave':
                        $quota->emergency_leave = max(0, $quota->emergency_leave - $daysUsed);
                        break;
                }

                $quota->used_days = ($quota->used_days ?? 0) + $daysUsed;
                $quota->save();
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
