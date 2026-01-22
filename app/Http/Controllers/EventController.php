<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index()
    {
        $events = Event::orderBy('start_date')->get();
        return view('events.index', compact('events'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'pic_phone'  => 'required|string|max:50',
            'location'   => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        Event::create($request->only([
            'event_name',
            'pic_phone',
            'location',
            'start_date',
            'end_date',
        ]));

        return redirect()->back()->with('success', 'Event added successfully!');
    }

    /**
     * Display the specified event (for popup view).
     */
    public function show($id)
    {
        $event = Event::findOrFail($id);
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event (for popup edit).
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'event_name' => 'required|string|max:255',
            'pic_phone'  => 'required|string|max:50',
            'location'   => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $event->update($request->only([
            'event_name',
            'pic_phone',
            'location',
            'start_date',
            'end_date',
        ]));

        return redirect()->route('holidays.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy($id)
    {
        Event::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Event deleted successfully!');
    }

    /**
     * Toggle the is_active status of an event.
     */
    public function toggle($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return redirect()->back()->with('error', 'Event not found!');
        }

        // Toggle active status
        $event->is_active = !$event->is_active;
        $event->save();

        return redirect()->back()->with('success', 'Event notification status updated!');
    }
}
