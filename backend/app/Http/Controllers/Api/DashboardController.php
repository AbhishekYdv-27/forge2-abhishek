<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        $orgId = $user->organization_id;

        $tickets = Ticket::where('organization_id', $orgId);

        return response()->json([
            'total_tickets' => $tickets->count(),
            'open_tickets' => $tickets->where('status', 'open')->count(),
            'pending_tickets' => $tickets->where('status', 'pending')->count(),
            'resolved_tickets' => $tickets->where('status', 'resolved')->count(),
            'closed_tickets' => $tickets->where('status', 'closed')->count(),
            'high_priority' => $tickets->where('priority', 'high')->count(),
            'my_assigned' => $tickets->where('assignee_id', $user->id)->count(),
            'recent_tickets' => $tickets->with(['requester', 'assignee'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
