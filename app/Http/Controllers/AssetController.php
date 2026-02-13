<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Floor;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::all();
        $floors = Floor::orderBy('floor_name')->get();

        confirmDelete('Delete', 'Are you sure you want to delete?');

        return view('asset.index', compact('assets', 'floors'));
    }

    public function store(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'asset_name'  => 'required',
            'floor_id'    => 'required',
            'serialNo'    => 'required',
            'location'    => 'required',
            'model'       => 'required',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'picture'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'asset_name.required' => 'Please fill this form',
            'floor_id.required'   => 'Please select a floor',
            'serialNo.required'   => 'Please fill this form',
            'location.required'   => 'Please fill this form',
            'model.required'      => 'Please fill this form',
        ]);

        // Get existing asset if editing
        $asset = Asset::find($id);

        // Handle image upload
        if ($request->hasFile('picture')) {

            // Delete old image if exists
            if ($asset && $asset->picture) {
                $oldPath = public_path('uploads/asset/' . $asset->picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/asset'), $filename);

            $imagePath = $filename;
        } else {
            $imagePath = $asset->picture ?? null;
        }

        Asset::updateOrCreate(
            ['id' => $id],
            [
                'asset_name'  => $request->asset_name,
                'floor_id'    => $request->floor_id,
                'serialNo'    => $request->serialNo,
                'location'    => $request->location,
                'model'       => $request->model,
                'latitude'    => $request->latitude, 
                'longitude'   => $request->longitude,
                'picture'     => $imagePath,
            ]
        );

        toast()->success('Successfully Saved!');
        return redirect()->back()->with('success', 'Asset updated.');
    }

    public function destroy(string $id)
    {
        $asset = Asset::findOrFail($id);

        // Delete image from public folder
        if ($asset->picture) {
            $path = public_path('uploads/asset/' . $asset->picture);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $asset->delete();

        toast()->success('Successfully Deleted!');
        return redirect()->route('master-data.assets.index');
    }

    public function show($id)
{
    $asset = Asset::findOrFail($id); // fetch asset from database
    return view('assets.show', compact('asset'));
}


}
