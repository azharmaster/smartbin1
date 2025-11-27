<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class TodoController extends Controller
{
    // This works because Controller extends the base Illuminate\Routing\Controller
    public function __construct()
    {
        $this->middleware('auth'); // Protect all routes
    }

    public function index()
    {
        $todos = Todo::where('userID', Auth::id())
                     ->where('status', 'pending')
                     ->orderBy('created_at', 'desc')
                     ->get();

        return view('dashboard.index', compact('todos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'todo' => 'required|string|max:255',
        ]);

        Todo::create([
            'todo' => $request->todo,
            'status' => 'pending',
            'userID' => Auth::id(),
        ]);

        return redirect()->back();
    }

    public function complete($id)
    {
        $todo = Todo::where('id', $id)
                    ->where('userID', Auth::id())
                    ->firstOrFail();

        $todo->status = 'done';
        $todo->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $todo = Todo::where('id', $id)
                    ->where('userID', Auth::id())
                    ->firstOrFail();

        $todo->delete();

        return redirect()->back();
    }
}