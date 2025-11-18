<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Display all tasks + data for modal
    public function index()
    {
        $tasks  = Task::with(['user','asset'])->get();
        $users  = User::all();
        $assets = Asset::all();

        return view('tasks.index', compact('tasks', 'users', 'assets'));
    }

    // Store new task
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id'  => 'required|exists:users,id',
            'description' => 'required|string|max:255',
        ]);

        Task::create($request->all());

        return redirect()->route('tasks.index')
                         ->with('success', 'Task created successfully!');
    }

    // Show a single task (optional)
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    // Show form to edit task
    public function edit(Task $task)
    {
        $users  = User::all();
        $assets = Asset::all();

        return view('tasks.edit', compact('task', 'users', 'assets'));
    }

    // Update task
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id'  => 'required|exists:users,id',
            'description' => 'required|string|max:255',
        ]);

        $task->update($request->all());

        return redirect()->route('tasks.index')
                         ->with('success', 'Task updated successfully!');
    }

    // Delete task
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
                         ->with('success', 'Task deleted successfully!');
    }
}
