<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::all();
        $floors = Floor::orderBy('floor_name')->get();
        $categories = Asset::pluck('category')->unique();

        confirmDelete('Delete', 'Are you sure you want to delete?');

        return view('asset.index', compact('assets', 'floors', 'categories'));
    }

    public function store(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'asset_name'  => 'required',
            'floor_id'    => 'required',
            'serialNo'    => 'required',
            'description' => 'required',
            'model'       => 'required',
            'maintenance' => 'required',
            'category'    => 'required',
            'picture'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'asset_name.required' => 'Please fill this form',
            'floor_id.required'   => 'Please select a floor',
            'serialNo.required'   => 'Please fill this form',
            'description.required'=> 'Please fill this form',
            'model.required'      => 'Please fill this form',
            'maintenance.required'=> 'Please fill this form',
            'category.required'   => 'Please select a category',
        ]);

        // Handle category
        $category = $request->category;
        if ($category === '__new__') {
            $category = $request->new_category;
        }

        // Get existing asset if editing
        $asset = Asset::find($id);

        // Handle image upload
        if ($request->hasFile('picture')) {

            // Delete old image if exists
            if ($asset && $asset->picture && Storage::disk('public')->exists($asset->picture)) {
                Storage::disk('public')->delete($asset->picture);
            }

            $imagePath = $request->file('picture')->store('assets', 'public');
        } else {
            $imagePath = $asset->picture ?? null;
        }

        Asset::updateOrCreate(
            ['id' => $id],
            [
                'asset_name'  => $request->asset_name,
                'floor_id'    => $request->floor_id,
                'serialNo'    => $request->serialNo,
                'description' => $request->description,
                'model'       => $request->model,
                'maintenance' => $request->maintenance,
                'category'    => $category,
                'picture'     => $imagePath,
            ]
        );

        toast()->success('Successfully Saved!');
        return redirect()->route('master-data.assets.index');
    }

    public function destroy(string $id)
    {
        $asset = Asset::findOrFail($id);

        // Delete image from storage
        if ($asset->picture && Storage::disk('public')->exists($asset->picture)) {
            Storage::disk('public')->delete($asset->picture);
        }

        $asset->delete();

        toast()->success('Successfully Deleted!');
        return redirect()->route('master-data.assets.index');
    }
}
