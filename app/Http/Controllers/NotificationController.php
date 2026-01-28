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

        // Exact calendar date range filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('sent_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }
        // Apply quick filter if set and no date range
        elseif ($request->filled('filter')) {
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
        $notifications = $query->orderBy('sent_at', 'desc')->paginate(10);

        return view('notification.index', compact('notifications'));
    }
}
