<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        if ($ticket->organization_id !== $request->user()->organization_id) {
            abort(403, 'Unauthorized');
        }

        return response()->json(
            $ticket->comments()->with('user')->latest()->get()
        );
    }

    public function store(Request $request, Ticket $ticket)
    {
        if ($ticket->organization_id !== $request->user()->organization_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'body' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $comment = $ticket->comments()->create([
            'body' => $data['body'],
            'user_id' => $request->user()->id,
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        return response()->json($comment->load('user'), 201);
    }
}