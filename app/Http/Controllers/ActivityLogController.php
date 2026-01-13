<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $query = ActivityLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->latest()->paginate(30);

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'user_id']),
        ]);
    }
}
