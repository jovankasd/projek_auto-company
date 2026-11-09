<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat mengakses audit log.');
        }

        $query = AuditLog::with('user');

        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $auditLogs = $query->latest('created_at')->paginate(20)->withQueryString();
        $events = AuditLog::distinct()->pluck('event');

        return view('dashboard.audit.index', compact('auditLogs', 'events'));
    }
}
