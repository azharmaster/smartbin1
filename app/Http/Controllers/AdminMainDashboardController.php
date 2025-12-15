<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Asset;

class AdminMainDashboardController extends Controller
{
    public function index()
    {
        // Get all floors
        $floors = Floor::all();

        // Get assets that have coordinates (for map markers)
        $assetsWithCoords = Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();

        return view('adminmaindashboard', compact(
            'floors',
            'assetsWithCoords'
        ));
    }
}
