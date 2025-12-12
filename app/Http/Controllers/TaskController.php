<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Asset;
use App\Models\Floor;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Display all tasks + data for modal
    public function index()
    {
        // Load tasks with relations and order by creation ascending for numbering
        $tasks  = Task::with(['user', 'asset', 'floor'])->orderBy('created_at', 'asc')->get();
        $users  = User::all();
        $assets = Asset::all();
        $floors = Floor::all(); // for add/assign/edit modal

        // Build numbering based on oldest task first
        $taskNumbers = [];
        $counter = 1;
        foreach ($tasks as $t) {
            $taskNumbers[$t->id] = $counter++;
        }

        return view('tasks.index', compact('tasks', 'users', 'assets', 'floors', 'taskNumbers'));
    }

    // Store new assigned task
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'asset_id' => 'required|exists:assets,id',
            'floor_id' => 'required|exists:floor,id',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Task::create([
            'user_id' => $request->user_id,
            'asset_id' => $request->asset_id,
            'floor_id' => $request->floor_id,
            'description' => $request->description,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task assigned successfully!');
    }

    // Show a single task (optional)
    public function show(Task $task)
    {
        $task->load(['user', 'asset', 'floor']); // eager load
        return view('tasks.show', compact('task'));
    }

    // Show form to edit task
    public function edit(Task $task)
    {
        $users  = User::all();
        $assets = Asset::all();
        $floors = Floor::all();

        return view('tasks.edit', compact('task', 'users', 'assets', 'floors'));
    }

    // Update task
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id'  => 'required|exists:users,id',
            'floor_id' => 'required|exists:floor,id',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,accepted,rejected,in_progress,completed',
        ]);

        $task->update($request->only('asset_id','user_id','floor_id','description','notes','status'));

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    // Delete task
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }
}
