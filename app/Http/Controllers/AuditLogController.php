<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filter by model type
        if ($request->filled('model')) {
            $query->where('auditable_type', $request->model);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get unique model types for filter dropdown
        $modelTypes = AuditLog::select('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->map(fn($type) => [
                'value' => $type,
                'label' => class_basename($type),
            ]);

        // Get users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('audit-logs.index', compact('logs', 'modelTypes', 'users'));
    }
}
