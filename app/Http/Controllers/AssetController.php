<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Floor;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        // All assets
        $assets = Asset::all();
        $floors = Floor::all(); 

        // Get unique categories from the assets table
        $categories = Asset::pluck('category')->unique();

        // Floor list for dropdown
        $floors = Floor::orderBy('floor_name')->get();

        confirmDelete('Delete', 'Are you sure you want to delete?');

        return view('asset.index', compact('assets', 'floors', 'categories'));
    }



    public function store(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'asset_name' => 'required',
            'floor_id' => 'required',
            'serialNo' => 'required',
            'description' => 'required',
            'model' => 'required',
            'maintenance' => 'required',
            'category' => 'required',
        ], [
            'asset_name.required' => 'Please fill this form',
            'floor_id.required' => 'Please select a floor',
            'serialNo.required' => 'Please fill this form',
            'description.required' => 'Please fill this form',
            'model.required' => 'Please fill this form',
            'maintenance.required' => 'Please fill this form',
            'category.required' => 'Please select a category',
        ]);

        Asset::updateOrCreate(
            ['id' => $id],
            [
                'asset_name' => $request->asset_name,
                'floor_id' => $request->floor_id,
                'serialNo' => $request->serialNo,
                'description' => $request->description,
                'model' => $request->model,
                'maintenance' => $request->maintenance,
                'category' => $request->category,
            ]
        );

        toast()->success('Successfully Saved!');
        return redirect()->route('master-data.assets.index');
    }



    public function destroy(String $id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();

        toast()->success('Successfully Deleted!');
        return redirect()->route('master-data.assets.index');
    }
}
