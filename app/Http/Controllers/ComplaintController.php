<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    // Show all complaints
    public function index()
    {
        $complaints = Complaint::with('asset')->latest()->paginate(10);
        $assets = \App\Models\Asset::all(); // Fetch all assets
        $staffs = \App\Models\User::where('role', 2)->get(); // Fetch all staff (role=3)

        return view('complaints.index', compact('complaints', 'assets', 'staffs'));
    }

    // Show create form
    public function create()
    {
        $assets = \App\Models\Asset::all();
        return view('complaints.create', compact('assets'));
    }

    // Store new complaint
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $validated['status'] = 'pending'; // default status

        Complaint::create($validated);

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint submitted successfully.');
    }

    // Show a single complaint
    public function show($id)
    {
        $complaint = Complaint::with('asset')->findOrFail($id);
        return view('complaints.show', compact('complaint'));
    }

    // Show edit form
    public function edit($id)
    {
        $complaint = Complaint::findOrFail($id);
        return view('complaints.edit', compact('complaint'));
    }

    // Update complaint
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'asset_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|in:pending,in_progress,completed', // allow status update
            'staff_id' => 'nullable|exists:users,id', // assign staff if provided
        ]);

        $complaint = Complaint::findOrFail($id);
        $complaint->update($validated);

        // Optionally, create a StaffTask record if staff_id is provided
        if (isset($validated['staff_id'])) {
            \App\Models\StaffTask::create([
                'complaint_id' => $complaint->id,
                'staff_id' => $validated['staff_id'],
                'status' => 'pending',
            ]);
        }

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint updated successfully.');
    }

    // Delete a complaint
    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted successfully.');
    }

    // Guest form
    public function guestForm()
    {
        $assets = \App\Models\Asset::all();
        return view('complaints.guest-form', compact('assets'));
    }

    // Guest submission
    public function guestSubmit(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $validated['status'] = 'pending'; // default status

        Complaint::create($validated);

        return redirect()->back()->with('success', 'Your complaint has been submitted.');
    }
}
