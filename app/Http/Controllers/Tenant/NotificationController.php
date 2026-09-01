<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(15)->through(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'info',
                'title' => $notification->data['title'] ?? 'Notification',
                'message' => $notification->data['message'] ?? '',
                'action_url' => $notification->data['action_url'] ?? null,
                'action_text' => $notification->data['action_text'] ?? null,
                'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
                'created_at' => $notification->created_at->diffForHumans(),
                'created_at_formatted' => $notification->created_at->format('M j, Y H:i'),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'Notification deleted.');
    }
}
