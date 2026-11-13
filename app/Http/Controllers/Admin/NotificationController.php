<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->with('user')
            ->latest()
            ->paginate(15);
        $unreadNotificationCount = $request->user()->notifications()->unread()->count();

        return view('backend.admin.notifications.index', compact('notifications', 'unreadNotificationCount'));
    }

    public function create()
    {
        $users = User::all();
        return view('backend.admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|in:booking,payment,system,general',
        ]);

        Notification::create($validated);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notifikasi berhasil dikirim.');
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        $updatedCount = $request->user()->notifications()
            ->unread()
            ->update(['is_read' => true]);

        return back()->with(
            'success',
            $updatedCount > 0
                ? 'Semua notifikasi telah ditandai sudah dibaca.'
                : 'Tidak ada notifikasi baru.'
        );
    }

    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->delete();

        return back()->with('success', 'Notifikasi dihapus.');
    }
}