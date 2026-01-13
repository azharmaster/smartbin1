<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationOff;
use Carbon\Carbon;

class NotificationOffController extends Controller
{
    /**
     * Store Notification OFF schedule for selected bins
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'end_time'   => 'required|date_format:H:i',
            'bins'       => 'required|array|min:1',
            'bins.*'     => 'exists:assets,id', // each selected bin must exist
        ]);

        // Combine date & time into Carbon instances
        $start_at = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $end_at   = Carbon::parse($request->end_date . ' ' . $request->end_time);

        // Loop through each selected bin and create NotificationOff
        foreach ($request->bins as $bin_id) {
            NotificationOff::create([
                'asset_id' => $bin_id,
                'start_at' => $start_at,
                'end_at'   => $end_at,
            ]);
        }

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification OFF schedule has been set for the selected bins.');
    }
}
