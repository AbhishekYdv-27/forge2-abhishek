<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['requester', 'assignee'])
            ->where('organization_id', $request->user()->organization_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorizeTenant($request, $ticket);
        return response()->json($ticket->load(['requester', 'assignee', 'comments.user']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assignee_id' => 'nullable|exists:users,id',
            'tags'        => 'nullable|array',
        ]);

        $ticket = Ticket::create([
            ...$data,
            'organization_id' => $request->user()->organization_id,
            'requester_id'    => $request->user()->id,
            'status'          => 'open',
        ]);

        return response()->json($ticket->load(['requester', 'assignee']), 201);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTenant($request, $ticket);

        $data = $request->validate([
            'subject'     => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|in:open,pending,resolved,closed',
            'priority'    => 'sometimes|in:low,medium,high,urgent',
            'assignee_id' => 'nullable|exists:users,id',
            'tags'        => 'nullable|array',
        ]);

        $ticket->update($data);

        return response()->json($ticket->load(['requester', 'assignee']));
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        $this->authorizeTenant($request, $ticket);
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted']);
    }

    private function authorizeTenant(Request $request, Ticket $ticket)
    {
        if ($ticket->organization_id !== $request->user()->organization_id) {
            abort(403, 'Unauthorized');
        }
    }
}