<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CapacitySetting;
use App\Models\Asset;

class CapacityController extends Controller
{
    // Show the capacity page
    public function index()
    {
        $assets = Asset::all();

        // Get all capacities keyed by asset_id
        $capacities = CapacitySetting::all()->keyBy('asset_id');

        return view('capacity.index', compact('assets', 'capacities'));
    }

    // Update capacity (single asset or bulk)
    public function update(Request $request)
    {
        $request->validate([
            'empty_to' => 'required|integer|min:0|max:98',
            'half_to'  => 'required|integer|min:1|max:99',
            'asset_id' => 'nullable|exists:assets,id',
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

        // If "apply to all" is checked, update all bins
        if ($request->has('apply_all') && $request->apply_all) {
            CapacitySetting::query()->update([
                'empty_to' => $emptyTo,
                'half_to'  => $halfTo,
            ]);
        } else {
            // Update or create the selected bin's capacity
            CapacitySetting::updateOrCreate(
                ['asset_id' => $request->asset_id],
                [
                    'empty_to' => $emptyTo,
                    'half_to'  => $halfTo,
                ]
            );
        }

        return back()->with('success', 'Capacity settings updated successfully.');
    }
}
