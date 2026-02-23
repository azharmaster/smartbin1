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
        $notifications = $query->orderBy('sent_at', 'desc')
                               ->get()
                               ->unique('message_preview') // ensure each message is unique
                               ->values();                // reindex collection

        // paginate manually after unique
        $perPage = 10;
        $page = $request->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $notifications->forPage($page, $perPage),
            $notifications->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('notification.index', ['todayNotifications' => $paginated]);
    }
}