<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Task;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    // Show all complaints
    public function index()
    {
        $complaints = Complaint::with(['asset', 'assignedStaff'])->latest()->paginate(10);
        $assets = \App\Models\Asset::all();
        $staffs = \App\Models\User::where('role', 2)->get(); 

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

public function assignStaff(Request $request, Complaint $complaint)
{
    $request->validate([
        'staff_id' => 'required|exists:users,id',
    ]);

    if ($complaint->assigned_to) {
        return response()->json([
            'success' => false,
            'message' => 'This complaint is already assigned.'
        ]);
    }

    $staff = \App\Models\User::find($request->staff_id);

    $task = Task::create([
        'asset_id' => optional($complaint->asset)->id,
        'floor_id' => optional($complaint->asset)->floor_id,
        'user_id' => $staff->id,
        'description' => "Complaint: {$complaint->title} - {$complaint->description}",
        'status' => 'pending',
        'complaint_id' => $complaint->id,
    ]);

    $complaint->update([
        'assigned_to' => $staff->id,
        'status' => 'assigned',
    ]);

    return response()->json([
        'success' => true,
        'assigned_to' => $staff->name
    ]);
}

}
