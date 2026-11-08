<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => $request->user()
                ->notifications()
                ->latest()
                ->get(['id', 'title', 'message', 'type', 'is_read', 'created_at']),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data' => $notification->only(['id', 'is_read']),
        ]);
    }
}
