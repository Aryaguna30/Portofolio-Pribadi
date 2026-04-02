<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        $paginator   = Message::latest()->paginate(25);
        $unreadCount = Message::where('is_read', false)->count();

        return Inertia::render('Admin/Messages/Index', [
            'messages'    => $paginator->items(),
            'pagination'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'next_page_url'  => $paginator->nextPageUrl(),
                'prev_page_url'  => $paginator->previousPageUrl(),
            ],
            'unreadCount' => $unreadCount,
        ]);
    }

    public function show(Message $message)
    {
        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return Inertia::render('Admin/Messages/Show', [
            'message' => $message->fresh(),
        ]);
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->back()
            ->with('success', 'Pesan berhasil dihapus.');
    }
}
