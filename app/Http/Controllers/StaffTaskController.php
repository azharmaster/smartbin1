<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StaffTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

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

        if (!in_array($status, ['in_progress', 'completed'])) abort(400);

        $task->update(['status' => $status]);

        return redirect()->back()->with('success', 'Task status updated.');
    }

    // Update task status from dropdown
    public function updateStatus(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);

        $request->validate([
            'status' => 'required|in:pending,reject,in_progress,completed',
        ]);

        $task->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Task status updated.');
    }

    // Staff dashboard with chart
    public function dashboard()
    {
        $userId = auth()->id();

        // Get months of the current year
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create()->month($i)->format('F');
        }

        // Aggregate tasks by month and status for this staff only
        $tasks = Task::where('user_id', $userId)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month', 'status')
            ->get();

        $pendingPerMonth = array_fill(0, 12, 0);
        $completedPerMonth = array_fill(0, 12, 0);
        $rejectedPerMonth = array_fill(0, 12, 0);

        foreach ($tasks as $task) {
            $index = $task->month - 1;
            if ($task->status === 'pending') $pendingPerMonth[$index] = $task->count;
            if ($task->status === 'completed') $completedPerMonth[$index] = $task->count;
            if ($task->status === 'reject') $rejectedPerMonth[$index] = $task->count;
        }

        return view('staff.dashboard', compact(
            'months', 
            'pendingPerMonth', 
            'completedPerMonth', 
            'rejectedPerMonth'
        ));
    }
}
