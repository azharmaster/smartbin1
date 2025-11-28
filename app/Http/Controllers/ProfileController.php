<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}

