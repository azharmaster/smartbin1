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

    return view('complaints.index', compact('complaints', 'assets'));
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
        ]);

        $complaint = Complaint::findOrFail($id);
        $complaint->update($validated);

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

        Complaint::create($validated);

        return redirect()->back()->with('success', 'Your complaint has been submitted.');
    }
}
