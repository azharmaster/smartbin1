<?php

namespace App\Http\Controllers;

use App\Models\Leave;
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
     * View a single leave request detail.
     */
    public function show(Leave $leave)
    {
        $leave->load('user');
        return view('admin.leave.show', compact('leave'));
    }
}
