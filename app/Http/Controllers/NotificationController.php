<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationLog; // Make sure this model exists

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications with optional filters.
     */
    public function index(Request $request)
    {
        $query = NotificationLog::query();

        // Apply filter if set
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'day':
                    $query->whereDate('sent_at', today());
                    break;

                case 'week':
                    $query->whereBetween('sent_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;

                case 'month':
                    $query->whereMonth('sent_at', now()->month)
                          ->whereYear('sent_at', now()->year);
                    break;

                case 'year':
                    $query->whereYear('sent_at', now()->year);
                    break;
            }
        }

        // Order by latest
        $notifications = $query->orderBy('sent_at', 'desc')->paginate(20);

        return view('notification.index', compact('notifications'));
    }
}
