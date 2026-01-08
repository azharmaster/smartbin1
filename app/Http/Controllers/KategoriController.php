<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    public function index(){
        $kat = Kategori::all();
        confirmDelete('Hapus Data','Apakah anda yakin ingin menghapus data ini?');

        return view('kategori.index', compact('kat'));
    }
    

    public function store(Request $request){
     

      $id = $request->input('id');
      $request->validate([
        'nama_kategori' => 'required|unique:Kategoris,nama_kategori,'.$id,
        'deskripsi' => 'required|max:100|min:10',
      ],[
        'nama_kategori.required' => 'Nama kategori harus diisi',
        'nama_kategori.unique' => 'Nama kategori sudah ada',
        'deskripsi.required' => 'Deskripsi harus diisi',
        'deskripsi.max' => 'Deskripsi tidak boleh lebih dari 100 karakter',
        'deskripsi.min' => 'Deskripsi tidak boleh kurang dari 10 karakter',
      ]);
      Kategori::updateOrCreate(
        ['id' => $id],
        [
            'nama_kategori' => $request->input('nama_kategori'),
            'slug' => Str::slug($request->nama_kategori),
            'deskripsi' => $request->input('deskripsi'),
            
        ]
      );

      toast()->success('Data berhasil disimpan');
      return redirect()->route('master-data.kategori.index');
    }

    public function destroy(String $id){ 
     $kategori = Kategori::findOrFail($id);
     $kategori->delete();
     toast()->success('Data berhasil dihapus');
     return redirect()->route('master-data.kategori.index');
    }
}
