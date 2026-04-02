<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        $messages    = Message::latest()->get();
        $unreadCount = Message::where('is_read', false)->count();

        return Inertia::render('Admin/Messages/Index', [
            'messages'    => $messages,
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
