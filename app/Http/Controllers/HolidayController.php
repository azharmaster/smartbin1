<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    /**
     * Display a listing of the holidays.
     */
    public function index()
    {
        // Fetch all holidays, ordered by holiday_date descending
        $holidays = Holiday::orderBy('holiday_date', 'desc')->get();

        return view('holidays.index', compact('holidays'));
    }

    /**
     * Store a new holiday.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'nullable|boolean',
        ]);

        Holiday::create([
            'name'         => $request->name,
            'holiday_date' => $request->start_date, // first day of holiday
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date ?? $request->start_date, // fallback to start_date
            'is_active'    => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('holidays.index')->with('success', 'Holiday added successfully.');
    }

    /**
     * Update an existing holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'nullable|boolean',
        ]);

        $holiday->update([
            'name'         => $request->name,
            'holiday_date' => $request->start_date, // ensure main holiday_date is updated
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date ?? $request->start_date,
            'is_active'    => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }
}
