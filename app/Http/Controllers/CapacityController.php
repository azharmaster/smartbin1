<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CapacitySetting;

class CapacityController extends Controller
{
    // Show the capacity page
    public function index()
    {
        $capacity = CapacitySetting::first();

        // Create default if not exists
        if (!$capacity) {
            $capacity = CapacitySetting::create([
                'empty_to' => 39,
                'half_to' => 79,
            ]);
        }

        return view('capacity.index', compact('capacity'));
    }

    // Update the capacity settings
    public function update(Request $request, $id)
    {
        $request->validate([
            'empty_to' => 'required|integer|min:1|max:98',
            'half_to'  => 'required|integer|min:2|max:99',
        ]);

        $emptyTo = $request->empty_to;
        $halfTo  = $request->half_to;

        // Validation: half must be greater than empty
        if ($halfTo <= $emptyTo) {
            return back()->withErrors([
                'half_to' => 'Half-Full range must be greater than Empty range.',
            ]);
        }

        // Validation: half must be less than 100
        if ($halfTo >= 100) {
            return back()->withErrors([
                'half_to' => 'Half-Full must be less than 100 so Full has a valid range.',
            ]);
        }

        $capacity = CapacitySetting::findOrFail($id);
        $capacity->update([
            'empty_to' => $emptyTo,
            'half_to' => $halfTo,
        ]);

        // Flash success message
        return back()->with('success', 'Capacity settings updated successfully.');
    }
}
