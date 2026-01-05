<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;

class UserController extends Controller
{
    public function index(){
        $users = User::all();
        confirmDelete('Hapus Data','Apakah abda yakin nak menghapus data ini?');

        return view('user.index', compact('users'));
    }

    public function store(Request $request){
        $id = $request->input('id');
        $request->validate([
            'name' => 'required|unique:users,name,'.$id,
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'nullable|string|max:20', // Added phone validation
        ],[
            'name.required' => 'Nama kategori harus diisi',
            'name.unique' => 'Nama kategori sudah ada',
            'email' => 'required|email|max:255',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter',
            'email.unique' => 'Email sudah terdaftar'
        ]);

        $data = $request->all();

        if(!$id){
            $data['password'] = Hash::make('12345678');
        }

        User::updateOrCreate(
            ['id' => $id],
            $data
        );

        toast()->success('Data berhasil disimpan');
        return redirect()->route('users.index');
    }

    public function destroy(String $id){ 
        $user = User::findOrFail($id);
        $user->delete();
        toast()->success('Data berhasil dihapus');
        return redirect()->route('users.index');
    }

    public function details(User $user)
    {
        // Optional: security check
        if (auth()->user()->role != 1) {
            abort(403);
        }

        return view('user.details', compact('user'));
    }

    public function resetPassword(User $user)
    {
        // Only admin allowed
        if (auth()->user()->role !== 1) {
            abort(403);
        }

        $user->password = Hash::make('12345678');
        $user->save();

        return back()->with('success', 'Password has been reset to default (12345678)');
    }
}
