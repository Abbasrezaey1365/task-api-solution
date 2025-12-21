<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unseen(Request $request)
    {
        return response()->json([
            'data' => $request->user()
                ->unreadNotifications()
                ->latest()
                ->paginate(15),
        ]);
    }

    public function markAllSeen(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'data' => ['success' => true],
        ]);
    }

    public function markOneSeen(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'data' => ['success' => true],
        ]);
    }
}
