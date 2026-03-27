<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Event;
use App\Http\Controllers\SummaryController;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of the holidays and events.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('start_date', 'asc')->get();
        $events   = Event::orderBy('start_date', 'asc')->get();

        return view('holidays.index', compact('holidays', 'events'));
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
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('holidays.index')
                         ->with('success', 'Holiday added successfully.');
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
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('holidays.index')
                         ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Delete a holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')
                         ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Toggle holiday is_active.
     */
    public function toggle($id)
    {
        $holiday = Holiday::find($id);

        if (!$holiday) {
            return redirect()->back()->with('error', 'Holiday not found.');
        }

        // Toggle is_active
        $holiday->is_active = !$holiday->is_active;
        $holiday->save();

        return redirect()->back()->with('success', 'Holiday notification status updated!');
    }

    public function binSummary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? $validated['start_date'])->endOfDay();

        $summaryController = app(SummaryController::class);
        $summary = $summaryController->getSummaryForRange($startDate, $endDate);

        return response()->json([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'summary_metrics' => $summary->summary_metrics,
            'bin_analytics' => $summary->bin_analytics,
            'cleaning_logs' => $summary->cleaning_logs->map(function ($log) {
                return [
                    'asset_name' => $log->asset_name,
                    'device_name' => $log->device_name,
                    'cleaned_at' => Carbon::parse($log->cleaned_at)->format('d M Y, h:i A'),
                ];
            })->values(),
            'top_bins' => $summary->top_bins->values(),
        ]);
    }
}
