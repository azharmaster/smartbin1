<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class StaffTaskController extends Controller
{
    public function __construct()
    {
        // Ensure user is authenticated
        $this->middleware('auth');

        // Only allow staff (role = 2)
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role != 2) {
                abort(403, 'Unauthorized. Only staff allowed.');
            }
            return $next($request);
        });
    }

    // Show all tasks assigned to logged-in staff
    public function index()
    {
        $tasks = Task::where('user_id', auth()->id())->get();
        return view('staff.tasks.index', compact('tasks'));
    }

    // Staff accepts task
    public function accept(Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);

        $task->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        return redirect()->back()->with('success', 'Task accepted.');
    }

    // Staff rejects task
    public function reject(Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);

        $task->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Task rejected.');
    }

    // Staff updates progress
    public function updateProgress(Task $task, $status)
    {
        if ($task->user_id !== auth()->id()) abort(403);

        if (!in_array($status, ['in_progress', 'done'])) abort(400);

        $task->update(['status' => $status]);

        return redirect()->back()->with('success', 'Task status updated.');
    }

    // New: Update task status from dropdown
    public function updateStatus(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);

        $request->validate([
            'status' => 'required|in:pending,reject,in progress,completed',
        ]);

        $task->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Task status updated.');
    }
}
