<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        return view('auth.login');
    } 
    
    public function handleLogin(Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ],[
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Email tidak valid',
            'password.required' => 'Password wajib diisi',
        ]);

        if(Auth::attempt($credentials)){ 

            $request->session()->regenerate();

            $user = Auth::user();

            // Role 1 = Admin → existing dashboard
            if ($user->role == 1) {
                return redirect('/dashboard');
            }

            // Role 2 = Staff
            if ($user->role == 2) {
                return redirect()->route('staff.dashboard');
            }

            // Role 3 = Guest
            if ($user->role == 3) {
                return redirect()->route('guest.dashboard');
            }

            // fallback if role undefined
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credential tidak sesuai',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('status', 'You have been logged out successfully.');
    }
}
