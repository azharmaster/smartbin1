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
            'picture' => 'required|max:100|min:10',
        ],[
            'floor_name.required' => 'Nama tingkat harus diisi',
            'floor_name.unique' => 'Nama tingkat sudah ada',
        ]);

        Floor::updateOrCreate(
            ['id' => $id],
            [
                'floor_name' => $request->input('floor_name'),
                'picture' => $request->input('picture'),
            ]
        );

        toast()->success('Data berhasil disimpan');
        return redirect()->route('floors.index');
    }

    public function destroy(String $id){ 
        $floor = Floor::findOrFail($id);
        $floor->delete();
        toast()->success('Data berhasil dihapus');
        return redirect()->route('floors.index');
    }
}
