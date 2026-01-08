<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        // Create folder if not exists
        $path = public_path('uploads/profile');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Generate unique filename
        $filename = time().'_'.$user->id.'.'.$request->profile_photo->extension();

        // Move file
        $request->profile_photo->move($path, $filename);

        // Save to database
        $user->profile_photo = $filename;
        $user->save();

        return back()->with('success', 'Profile photo updated!');
    }
public function editPassword()
{
    $user = auth()->user();

    // Adjust role values if yours differ
    if ($user->role == 2) { // Staff
        return view('profile.staffpassword');
    }

    // Default: Admin
    return view('profile.password');
}

public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = auth()->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Current password is incorrect']);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return back()->with('success', 'Password updated successfully!');
}

public function update(Request $request)
{
    $user = Auth::user();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->save();

    return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
}

}

