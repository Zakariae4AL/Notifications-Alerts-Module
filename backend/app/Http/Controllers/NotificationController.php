<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /notifications
    public function index(Request $request)
    {
        return $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // POST /notifications/trigger
    public function trigger(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'user_ids' => 'required|array',
            'message' => 'required',
            'channels' => 'required|array'
        ]);

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $pref = $user->notificationPreferences;
            if (!$pref || !$pref->{$request->type}) continue;

            foreach ($request->channels as $channel) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => $request->type,
                    'message' => $request->message,
                    'channel' => $channel,
                    'status' => 'pending'
                ]);
            }
        }

        return response()->json(['message' => 'Notifications triggered']);
    }

    // PUT /notifications/{id}/read
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }
}
