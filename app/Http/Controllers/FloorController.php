<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
     public function index(){
        $floors = Floor::all();
        confirmDelete('Delete','Are sure want to delete?');

        return view('floor.index', compact('floors'));
    }

    public function store(Request $request)
{
    $id = $request->input('id');

    $request->validate([
    'floor_name' => 'required|unique:floor,floor_name,' . $id . ',id',
    'picture' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048', // max 2MB
    ],[
        'floor_name.required' => 'Please enter floor name',
        'floor_name.unique' => 'Floor Name already existed',
        'picture.image' => 'File has to be a picture',
        'picture.mimes' => 'Only PNG, JPG, JPEG, WEBP files are accepted',
        'picture.max' => 'File must be below or equal to 2MB',
    ]);

    $data = [
        'floor_name' => $request->input('floor_name'),
    ];

   // Handle file upload
    if ($request->hasFile('picture')) {
        $file = $request->file('picture');  // get uploaded file
        $filename = time() . '_' . $file->getClientOriginalName(); // unique filename
        $file->storeAs('public/floor_pictures', $filename); // save to storage/app/public/floor_pictures
        $data['picture'] = $filename; // save filename in DB
    }

    Floor::updateOrCreate(
        ['id' => $id],
        $data
    );

    toast()->success('Successfully saved!');
    return redirect()->route('floors.index');
}
    public function destroy(String $id){ 
        $floor = Floor::findOrFail($id);
        $floor->delete();
        toast()->success('Successfully deleted!');
        return redirect()->route('floors.index');
    }
}
