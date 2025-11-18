<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Floor;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // Display all schedules
    public function index()
    {
        $schedules = Schedule::with(['user', 'floor'])->get();
        $users = User::all();
        $floors = Floor::all();

        // Note: folder is singular 'schedule'
        return view('schedule.index', compact('schedules', 'users', 'floors'));
    }

    // Store a new schedule
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'floor_id' => 'required|exists:floor,id', // updated to singular
            'shift' => 'required|string',
            'date' => 'required|date',
        ]);

        Schedule::create($request->all());

        return redirect()->back()->with('success', 'Schedule added successfully.');
    }

    // Update an existing schedule
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'floor_id' => 'required|exists:floor,id', // updated to singular
            'shift' => 'required|string',
            'date' => 'required|date',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());

        return redirect()->back()->with('success', 'Schedule updated successfully.');
    }

    // Delete a schedule
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }
}
